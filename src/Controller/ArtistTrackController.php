<?php

namespace App\Controller;

use App\Entity\ArtistProfile;
use App\Entity\Track;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArtistTrackController extends AbstractController
{
    #[Route('/artist/{slug}', name: 'app_artist_public', methods: ['GET'], requirements: ['slug' => '^(?!(?:dashboard|profile|tracks)$)[a-z0-9-]+$'])]
    public function publicProfile(string $slug, EntityManagerInterface $entityManager): Response
    {
        $profile = $entityManager->getRepository(ArtistProfile::class)->findOneBy(['slug' => strtolower(trim($slug))]);

        if (!$profile) {
            throw $this->createNotFoundException('This artist is not found.');
        }

        $tracks = $entityManager->getRepository(Track::class)->findBy(['artist' => $profile->getUser()], ['createdAt' => 'DESC']);

        return $this->render('artist/public_profile.html.twig', [
            'profile' => $profile,
            'tracks' => $tracks,
        ]);
    }

    #[Route('/artist/tracks', name: 'app_artist_tracks', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Veuillez vérifier votre compte avant d’ajouter un morceau.');

            return $this->redirectToRoute('app_verify', ['email' => $user->getEmail()], Response::HTTP_SEE_OTHER);
        }

        $tracks = $entityManager->getRepository(Track::class)->findBy(['artist' => $user], ['createdAt' => 'DESC']);

        return $this->render('artist/tracks.html.twig', [
            'tracks' => $tracks,
        ]);
    }

    #[Route('/artist/tracks/new', name: 'app_artist_tracks_new', methods: ['GET', 'POST'], defaults: ['_profiler_collect' => false])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Veuillez vérifier votre compte avant d’ajouter un morceau.');

            return $this->redirectToRoute('app_verify', ['email' => $user->getEmail()], Response::HTTP_SEE_OTHER);
        }

        if (!$user->getArtistProfile()) {
            $this->addFlash('info', 'Complétez d’abord votre profil artiste.');

            return $this->redirectToRoute('app_artist_profile', [], Response::HTTP_SEE_OTHER);
        }

        $errors = [];
        $formData = [
            'title' => '',
        ];

        if ($request->isMethod('POST')) {
            $formData['title'] = trim((string) $request->request->get('title', ''));
            $audioFile = $request->files->get('audioFile');
            $coverImage = $request->files->get('coverImage');

            if ('' === $formData['title']) {
                $errors[] = 'Le titre du morceau est obligatoire.';
            }

            if (!$audioFile) {
                $contentLength = $request->headers->get('Content-Length');
                $size = null !== $contentLength ? filter_var($contentLength, FILTER_VALIDATE_INT) : false;

                if (false !== $size && $size > 80 * 1024 * 1024) {
                    $errors[] = 'Le fichier audio n’a pas été reçu par le serveur. Vérifiez qu’il ne dépasse pas la limite de téléchargement autorisée (80 Mo max).';
                } else {
                    $errors[] = 'Le fichier audio est obligatoire.';
                }
            } elseif (!$audioFile->isValid()) {
                $errors[] = sprintf('Le fichier audio n’a pas pu être téléversé: %s', $audioFile->getErrorMessage());
            } else {
                $allowedAudioTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/aac', 'audio/ogg'];
                $allowedAudioExtensions = ['mp3', 'wav', 'aac', 'ogg'];
                $extension = strtolower(pathinfo($audioFile->getClientOriginalName(), PATHINFO_EXTENSION));

                if (!in_array($audioFile->getMimeType(), $allowedAudioTypes, true) && !in_array($extension, $allowedAudioExtensions, true)) {
                    $errors[] = 'Le fichier audio doit être un MP3, WAV, AAC ou OGG.';
                }

                if ($audioFile->getSize() > 80 * 1024 * 1024) {
                    $errors[] = 'Le fichier audio ne doit pas dépasser 80 Mo.';
                }
            }

            if ($coverImage && !$coverImage->isValid()) {
                $errors[] = sprintf('La pochette n’a pas pu être téléversée: %s', $coverImage->getErrorMessage());
            } elseif ($coverImage) {
                $allowedImageMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $extension = strtolower(pathinfo($coverImage->getClientOriginalName(), PATHINFO_EXTENSION));

                if (!in_array($coverImage->getMimeType(), $allowedImageMimeTypes, true) && !in_array($extension, $allowedImageExtensions, true)) {
                    $errors[] = 'La pochette doit être une image JPG, PNG ou WebP.';
                }

                if ($coverImage->getSize() > 5 * 1024 * 1024) {
                    $errors[] = 'La pochette ne doit pas dépasser 5 Mo.';
                }
            }

            if ([] === $errors) {
                $track = new Track();
                $track->setArtist($user);
                $track->setTitle($formData['title']);

                if ($audioFile) {
                    $track->setAudioFile($audioFile);
                }

                if ($coverImage) {
                    $track->setCoverImage($coverImage);
                }

                $entityManager->persist($track);
                $entityManager->flush();

                $this->addFlash('success', 'Le morceau a bien été ajouté.');

                return $this->redirectToRoute('app_artist_tracks', [], Response::HTTP_SEE_OTHER);
            }

            $this->addFlash('error', 'Le morceau n’a pas pu être enregistré. Merci de corriger les erreurs ci-dessous.');
        }

        return $this->render('artist/track_form.html.twig', [
            'errors' => $errors,
            'formData' => $formData,
        ]);
    }
}
