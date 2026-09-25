<?php

namespace App\Controller;

use App\Repository\ArtistRepository;
use App\Repository\TrackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArtistDetailController extends AbstractController
{
    #[Route('/artists/{slug}', name: 'app_artist_detail')]
    public function index(string $slug, ArtistRepository $artistRepository, TrackRepository $trackRepository): Response
    {
        $artist = $artistRepository->findOneBy(['slug' => $slug]);

        if (!$artist) {
            throw $this->createNotFoundException('Artiste introuvable.');
        }

        $currentUser = $this->getUser();
        $isOwner = $currentUser !== null && $artist->getUser() === $currentUser;

        if (!$artist->isPublished() && !$isOwner) {
            throw $this->createNotFoundException('Artiste introuvable.');
        }

        $tracks = $trackRepository->findBy([
            'artist' => $artist,
            'isPublished' => true,
        ], [
            'createdAt' => 'DESC',
        ]);

        return $this->render('artists/detail.html.twig', [
            'artist' => $artist,
            'tracks' => $tracks,
        ]);
    }
}
