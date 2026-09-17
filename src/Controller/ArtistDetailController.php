<?php

namespace App\Controller;

use App\Repository\ArtistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArtistDetailController extends AbstractController
{
    #[Route('/artists/{slug}', name: 'app_artist_detail')]
    public function index(string $slug, ArtistRepository $artistRepository): Response
    {
        $artist = $artistRepository->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$artist) {
            throw $this->createNotFoundException('Artiste introuvable.');
        }

        $featuredTracks = [
            'Midnight Echo',
            'Night Bloom',
            'Electric Veil',
        ];

        return $this->render('artists/detail.html.twig', [
            'artist' => $artist,
            'featured' => $featuredTracks,
        ]);
    }
}
