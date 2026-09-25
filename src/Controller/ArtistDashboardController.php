<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Track;
use App\Form\ArtistType;
use App\Form\TrackType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ArtistDashboardController extends AbstractController
{
    #[Route('/become-artist', name: 'app_become_artist')]
    #[IsGranted('ROLE_USER')]
    public function becomeArtist(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour devenir artiste.');
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('admin');
        }

        $user->setAccountType('artist');
        $user->setRoles(['ROLE_USER', 'ROLE_ARTISTE']);
        $doctrine->getManager()->flush();

        $this->addFlash('success', 'Votre compte a bien été mis à jour en tant qu’artiste.');

        return $this->redirectToRoute('app_artist_profile_new');
    }

    #[Route('/artist/dashboard', name: 'app_artist_dashboard')]
    #[IsGranted('ROLE_ARTISTE')]
    public function dashboard(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('admin');
        }

        $artists = $doctrine->getRepository(Artist::class)->findBy([
            'user' => $user,
        ]);

        $tracksByArtist = [];
        foreach ($artists as $artist) {
            $tracksByArtist[$artist->getId()] = $doctrine->getRepository(Track::class)->findBy([
                'artist' => $artist,
            ], [
                'createdAt' => 'DESC',
            ]);
        }

        return $this->render('artist/dashboard.html.twig', [
            'artists' => $artists,
            'tracksByArtist' => $tracksByArtist,
        ]);
    }

    #[Route('/artist/profile/new', name: 'app_artist_profile_new')]
    #[IsGranted('ROLE_ARTISTE')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('admin');
        }

        $artist = new Artist();
        $artist->setUser($user);

        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artist->setIsPublished(false);
            $doctrine->getManager()->persist($artist);
            $doctrine->getManager()->flush();

            $this->addFlash('success', 'Votre profil artiste a été enregistré et est en attente de validation.');

            return $this->redirectToRoute('app_artist_dashboard');
        }

        return $this->render('artist/profile_new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/artist/{id}/track/new', name: 'app_artist_track_new')]
    #[IsGranted('ROLE_ARTISTE')]
    public function newTrack(int $id, Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('admin');
        }

        $artist = $doctrine->getRepository(Artist::class)->find($id);

        if (!$artist || $artist->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas gérer ce profil artiste.');
        }

        $track = new Track();
        $track->setArtist($artist);
        $track->setCreatedAt(new \DateTimeImmutable());
        $track->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(TrackType::class, $track);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $track->setCreatedAt(new \DateTimeImmutable());
            $track->setUpdatedAt(new \DateTimeImmutable());

            $audioFile = $form->get('audioFile')->getData();
            if ($audioFile instanceof UploadedFile) {
                $track->setAudioUrl($this->uploadTrackAsset($audioFile, 'tracks'));
            } else {
                $track->setAudioUrl($form->get('audioUrl')->getData() ?: null);
            }

            $coverImage = $form->get('coverImage')->getData();
            if ($coverImage instanceof UploadedFile) {
                $track->setCoverImage($this->uploadTrackAsset($coverImage, 'covers'));
            }

            $publishMode = $form->get('publishMode')->getData();
            $publishedAt = $form->get('publishedAt')->getData();
            if ('scheduled' === $publishMode && $publishedAt instanceof \DateTimeInterface) {
                $scheduledDate = \DateTimeImmutable::createFromInterface($publishedAt);
                $track->setPublishedAt($scheduledDate);
                $track->setIsPublished($scheduledDate <= new \DateTimeImmutable());
            } else {
                $track->setPublishedAt(new \DateTimeImmutable());
                $track->setIsPublished(true);
            }

            $doctrine->getManager()->persist($track);
            $doctrine->getManager()->flush();

            $this->addFlash('success', 'Le morceau a bien été enregistré.');

            return $this->redirectToRoute('app_artist_dashboard');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Vérifiez les champs marqués et réessayez.');
        }

        return $this->render('artist/track_new.html.twig', [
            'form' => $form,
            'artist' => $artist,
        ]);
    }

    #[Route('/artist/track/{id}/edit', name: 'app_artist_track_edit')]
    #[IsGranted('ROLE_ARTISTE')]
    public function editTrack(int $id, Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('admin');
        }

        $track = $doctrine->getRepository(Track::class)->find($id);

        if (!$track || !$track->getArtist() || $track->getArtist()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce morceau.');
        }

        $form = $this->createForm(TrackType::class, $track);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $track->setUpdatedAt(new \DateTimeImmutable());

            $audioFile = $form->get('audioFile')->getData();
            if ($audioFile instanceof UploadedFile) {
                $track->setAudioUrl($this->uploadTrackAsset($audioFile, 'tracks'));
            } else {
                $track->setAudioUrl($form->get('audioUrl')->getData() ?: null);
            }

            $coverImage = $form->get('coverImage')->getData();
            if ($coverImage instanceof UploadedFile) {
                $track->setCoverImage($this->uploadTrackAsset($coverImage, 'covers'));
            }

            $publishMode = $form->get('publishMode')->getData();
            $publishedAt = $form->get('publishedAt')->getData();
            if ('scheduled' === $publishMode && $publishedAt instanceof \DateTimeInterface) {
                $scheduledDate = \DateTimeImmutable::createFromInterface($publishedAt);
                $track->setPublishedAt($scheduledDate);
                $track->setIsPublished($scheduledDate <= new \DateTimeImmutable());
            } else {
                $track->setPublishedAt(new \DateTimeImmutable());
                $track->setIsPublished(true);
            }

            $doctrine->getManager()->flush();

            $this->addFlash('success', 'Le morceau a bien été mis à jour.');

            return $this->redirectToRoute('app_artist_dashboard');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Vérifiez les champs marqués et réessayez.');
        }

        return $this->render('artist/track_edit.html.twig', [
            'form' => $form,
            'track' => $track,
        ]);
    }

    private function uploadTrackAsset(UploadedFile $file, string $directory): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))) ?? 'upload';
        $safeName = trim($safeName, '-');
        $fileName = $safeName . '-' . bin2hex(random_bytes(6)) . '.' . $file->guessExtension();
        $targetDirectory = __DIR__ . '/../../public/uploads/' . $directory;

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        $file->move($targetDirectory, $fileName);

        return '/uploads/' . $directory . '/' . $fileName;
    }

    #[Route('/artist/track/{id}/toggle-publish', name: 'app_artist_track_toggle_publish')]
    #[IsGranted('ROLE_ARTISTE')]
    public function togglePublish(int $id, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('admin');
        }

        $track = $doctrine->getRepository(Track::class)->find($id);

        if (!$track || !$track->getArtist() || $track->getArtist()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce morceau.');
        }

        $track->setIsPublished(!$track->isPublished());
        $track->setUpdatedAt(new \DateTimeImmutable());
        $doctrine->getManager()->flush();

        $this->addFlash('success', $track->isPublished() ? 'Le morceau est maintenant visible publiquement.' : 'Le morceau a été retiré de la publication.');

        return $this->redirectToRoute('app_artist_dashboard');
    }

    #[Route('/artist/track/{id}/delete', name: 'app_artist_track_delete')]
    #[IsGranted('ROLE_ARTISTE')]
    public function deleteTrack(int $id, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('admin');
        }

        $track = $doctrine->getRepository(Track::class)->find($id);

        if (!$track || !$track->getArtist() || $track->getArtist()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce morceau.');
        }

        $doctrine->getManager()->remove($track);
        $doctrine->getManager()->flush();

        $this->addFlash('success', 'Le morceau a bien été supprimé.');

        return $this->redirectToRoute('app_artist_dashboard');
    }
}
