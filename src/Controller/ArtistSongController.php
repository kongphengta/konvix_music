<?php

namespace App\Controller;

use App\Entity\Song;
use App\Entity\User;
use App\Form\ArtistSongFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/artist/songs')]
#[IsGranted('ROLE_ARTIST')]
class ArtistSongController extends AbstractController
{
    #[Route('/new', name: 'app_artist_song_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user   = $this->getUser();
        $artist = $user->getArtistProfile();

        $song = new Song();
        $form = $this->createForm(ArtistSongFormType::class, $song, [
            'require_uploads' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverUpload = $this->extractUploadedFile($request, 'cover_upload');
            $audioUpload = $this->extractUploadedFile($request, 'audio_upload');

            if (!$coverUpload instanceof UploadedFile) {
                $coverUpload = $this->uploadedFileFromServerPath((string) $request->request->get('cover_server_path', ''));
            }

            if (!$audioUpload instanceof UploadedFile) {
                $audioUpload = $this->uploadedFileFromServerPath((string) $request->request->get('audio_server_path', ''));
            }

            if (!$coverUpload instanceof UploadedFile || !$audioUpload instanceof UploadedFile) {
                [$fallbackCover, $fallbackAudio] = $this->findMediaUploads($request->files->all());
                $coverUpload = $coverUpload instanceof UploadedFile ? $coverUpload : $fallbackCover;
                $audioUpload = $audioUpload instanceof UploadedFile ? $audioUpload : $fallbackAudio;
            }

            if (!$coverUpload instanceof UploadedFile || !$audioUpload instanceof UploadedFile) {
                $coverPath = trim((string) $request->request->get('cover_server_path', ''));
                $audioPath = trim((string) $request->request->get('audio_server_path', ''));

                $this->addFlash(
                    'danger',
                    sprintf(
                        'Upload incomplet : image/audio non reçus. Le chemin NAS/serveur doit être visible par le serveur PHP (MOUNT + droits web). Cover=%s | Audio=%s | files_count=%d',
                        $coverPath !== '' ? $coverPath : 'non-renseigné',
                        $audioPath !== '' ? $audioPath : 'non-renseigné',
                        $request->files->count()
                    )
                );
                $this->addFlash(
                    'danger',
                    sprintf(
                        'Diagnostics PHP : cover_error=%s, audio_error=%s, content_type=%s.',
                        $this->uploadErrorLabel($this->readPhpUploadError('cover_upload')),
                        $this->uploadErrorLabel($this->readPhpUploadError('audio_upload')),
                        (string) $request->headers->get('content-type', 'n/a')
                    )
                );

                return $this->render('artist/song_new.html.twig', [
                    'form'   => $form,
                    'artist' => $artist,
                    'phpUploadMax' => ini_get('upload_max_filesize'),
                    'phpPostMax' => ini_get('post_max_size'),
                ]);
            }

            $song->setCoverFile($coverUpload);
            $song->setAudioFile($audioUpload);

            $slug = strtolower($slugger->slug($song->getTitle())->toString());

            // Gérer les slugs en double
            $base  = $slug;
            $i     = 1;
            while ($em->getRepository(Song::class)->findOneBy(['slug' => $slug])) {
                $slug = $base . '-' . $i++;
            }

            $song->setSlug($slug);
            $song->setArtist($artist);

            $em->persist($song);
            $em->flush();

            $this->addFlash('success', '🎵 "' . $song->getTitle() . '" a été publié avec succès !');

            return $this->redirectToRoute('app_artist_dashboard');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Publication echouee: verifiez les erreurs des champs image/audio ci-dessous.');
        }

        return $this->render('artist/song_new.html.twig', [
            'form'   => $form,
            'artist' => $artist,
            'phpUploadMax' => ini_get('upload_max_filesize'),
            'phpPostMax' => ini_get('post_max_size'),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_artist_song_edit')]
    public function edit(Song $song, Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($song->getArtist() !== $user->getArtistProfile()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ArtistSongFormType::class, $song, [
            'require_uploads' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverUpload = $this->extractUploadedFile($request, 'cover_upload');
            if (!$coverUpload instanceof UploadedFile) {
                $coverUpload = $this->uploadedFileFromServerPath((string) $request->request->get('cover_server_path', ''));
            }
            if (!$coverUpload instanceof UploadedFile) {
                [$fallbackCover] = $this->findMediaUploads($request->files->all());
                $coverUpload = $fallbackCover;
            }
            if ($coverUpload instanceof UploadedFile) {
                $song->setCoverFile($coverUpload);
            }

            $audioUpload = $this->extractUploadedFile($request, 'audio_upload');
            if (!$audioUpload instanceof UploadedFile) {
                $audioUpload = $this->uploadedFileFromServerPath((string) $request->request->get('audio_server_path', ''));
            }
            if (!$audioUpload instanceof UploadedFile) {
                [, $fallbackAudio] = $this->findMediaUploads($request->files->all());
                $audioUpload = $fallbackAudio;
            }
            if ($audioUpload instanceof UploadedFile) {
                $song->setAudioFile($audioUpload);
            }

            $em->flush();
            $this->addFlash('success', 'Chanson mise à jour.');
            return $this->redirectToRoute('app_artist_dashboard');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Mise a jour echouee: verifiez les erreurs des champs image/audio ci-dessous.');
        }

        return $this->render('artist/song_new.html.twig', [
            'form'   => $form,
            'artist' => $user->getArtistProfile(),
            'song'   => $song,
            'phpUploadMax' => ini_get('upload_max_filesize'),
            'phpPostMax' => ini_get('post_max_size'),
        ]);
    }

    /**
     * @param array<string, mixed> $files
     * @return array{0: ?UploadedFile, 1: ?UploadedFile}
     */
    private function findMediaUploads(array $files): array
    {
        $image = null;
        $audio = null;

        foreach ($this->flattenUploadedFiles($files) as $file) {
            if (!$file instanceof UploadedFile || $file->getError() !== \UPLOAD_ERR_OK) {
                continue;
            }

            $mime = strtolower((string) $file->getMimeType());
            $originalName = strtolower((string) $file->getClientOriginalName());

            if ($image === null && $this->looksLikeImage($mime, $originalName)) {
                $image = $file;
                continue;
            }

            if ($audio === null && $this->looksLikeAudio($mime, $originalName)) {
                $audio = $file;
            }
        }

        return [$image, $audio];
    }

    private function looksLikeImage(string $mime, string $originalName): bool
    {
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        foreach (['.jpg', '.jpeg', '.png', '.webp', '.gif', '.bmp', '.avif', '.svg'] as $extension) {
            if (str_ends_with($originalName, $extension)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeAudio(string $mime, string $originalName): bool
    {
        if (str_starts_with($mime, 'audio/')) {
            return true;
        }

        $haystack = $mime . ' ' . $originalName;

        foreach (['mp3', 'wav', 'x-wav', 'mpeg', 'aac', 'ogg', 'flac', 'audio'] as $token) {
            if (str_contains($haystack, $token)) {
                return true;
            }
        }

        foreach (['.mp3', '.wav', '.m4a', '.aac', '.ogg', '.flac'] as $extension) {
            if (str_ends_with($originalName, $extension)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $value
     * @return list<UploadedFile>
     */
    private function flattenUploadedFiles(mixed $value): array
    {
        if ($value instanceof UploadedFile) {
            return [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            foreach ($this->flattenUploadedFiles($item) as $file) {
                $result[] = $file;
            }
        }

        return $result;
    }

    private function extractUploadedFile(Request $request, string $field): ?UploadedFile
    {
        $fromBag = $request->files->get($field);

        if ($fromBag instanceof UploadedFile) {
            return $fromBag->getError() === \UPLOAD_ERR_OK ? $fromBag : null;
        }

        if (is_array($fromBag)) {
            foreach ($this->flattenUploadedFiles($fromBag) as $file) {
                if ($file->getError() === \UPLOAD_ERR_OK) {
                    return $file;
                }
            }
        }

        if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
            return null;
        }

        $error = isset($_FILES[$field]['error']) ? (int) $_FILES[$field]['error'] : \UPLOAD_ERR_NO_FILE;
        $tmpName = isset($_FILES[$field]['tmp_name']) ? (string) $_FILES[$field]['tmp_name'] : '';
        $originalName = isset($_FILES[$field]['name']) ? (string) $_FILES[$field]['name'] : 'upload.bin';
        $mime = isset($_FILES[$field]['type']) ? (string) $_FILES[$field]['type'] : null;

        if ($error !== \UPLOAD_ERR_OK || $tmpName === '' || !is_file($tmpName)) {
            return null;
        }

        return new UploadedFile($tmpName, $originalName, $mime, $error, true);
    }

    private function readPhpUploadError(string $field): int
    {
        if (!isset($_FILES[$field]) || !is_array($_FILES[$field]) || !isset($_FILES[$field]['error'])) {
            return \UPLOAD_ERR_NO_FILE;
        }

        return (int) $_FILES[$field]['error'];
    }

    private function uploadErrorLabel(int $error): string
    {
        return match ($error) {
            \UPLOAD_ERR_OK => 'OK',
            \UPLOAD_ERR_INI_SIZE => 'INI_SIZE',
            \UPLOAD_ERR_FORM_SIZE => 'FORM_SIZE',
            \UPLOAD_ERR_PARTIAL => 'PARTIAL',
            \UPLOAD_ERR_NO_FILE => 'NO_FILE',
            \UPLOAD_ERR_NO_TMP_DIR => 'NO_TMP_DIR',
            \UPLOAD_ERR_CANT_WRITE => 'CANT_WRITE',
            \UPLOAD_ERR_EXTENSION => 'EXTENSION',
            default => 'UNKNOWN_' . $error,
        };
    }

    private function uploadedFileFromServerPath(string $path): ?UploadedFile
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', $path);
        if (str_starts_with($normalized, 'file://')) {
            $normalized = preg_replace('#^file://#', '', $normalized) ?? $normalized;
        }

        if ($normalized === '' || !is_file($normalized) || !is_readable($normalized)) {
            return null;
        }

        $mime = mime_content_type($normalized) ?: null;

        return new UploadedFile(
            $normalized,
            basename($normalized),
            is_string($mime) ? $mime : null,
            \UPLOAD_ERR_OK,
            true
        );
    }
}
