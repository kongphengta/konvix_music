<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AlbumController extends AbstractController
{
    #[Route('/albums', name: 'app_albums')]
    public function index(): Response
    {
        $albums = [
            [
                'title' => 'Midnight Echo',
                'artist' => 'Luna Kairo',
                'year' => '2026',
                'tracks' => 12,
                'color' => 'violet',
            ],
            [
                'title' => 'Neon Skyline',
                'artist' => 'Noah Vale',
                'year' => '2025',
                'tracks' => 10,
                'color' => 'cyan',
            ],
            [
                'title' => 'Velvet Engine',
                'artist' => 'Sora Mint',
                'year' => '2024',
                'tracks' => 11,
                'color' => 'pink',
            ],
            [
                'title' => 'Afterglow Run',
                'artist' => 'Kian Drift',
                'year' => '2026',
                'tracks' => 9,
                'color' => 'gold',
            ],
            [
                'title' => 'Soft Circuit',
                'artist' => 'Mila Greys',
                'year' => '2025',
                'tracks' => 13,
                'color' => 'rose',
            ],
            [
                'title' => 'Night Bloom',
                'artist' => 'Aero Bloom',
                'year' => '2024',
                'tracks' => 8,
                'color' => 'green',
            ],
        ];

        return $this->render('albums/index.html.twig', [
            'albums' => $albums,
        ]);
    }
}
