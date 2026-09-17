<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlaylistController extends AbstractController
{
    #[Route('/playlists', name: 'app_playlists')]
    public function index(): Response
    {
        $playlists = [
            [
                'title' => 'Midnight Drive',
                'mood' => 'Night drive',
                'tracks' => 18,
                'duration' => '1h 11m',
                'color' => 'violet',
            ],
            [
                'title' => 'Velvet Signals',
                'mood' => 'Late-night pulse',
                'tracks' => 14,
                'duration' => '58m',
                'color' => 'cyan',
            ],
            [
                'title' => 'Sunset Fade',
                'mood' => 'Golden hour',
                'tracks' => 12,
                'duration' => '47m',
                'color' => 'pink',
            ],
            [
                'title' => 'After Hours Flow',
                'mood' => 'Deep focus',
                'tracks' => 21,
                'duration' => '1h 28m',
                'color' => 'gold',
            ],
            [
                'title' => 'Cloudline Dreams',
                'mood' => 'Dream pop',
                'tracks' => 16,
                'duration' => '1h 03m',
                'color' => 'rose',
            ],
            [
                'title' => 'Zero Gravity',
                'mood' => 'Elevation',
                'tracks' => 13,
                'duration' => '52m',
                'color' => 'green',
            ],
        ];

        return $this->render('playlists/index.html.twig', [
            'playlists' => $playlists,
        ]);
    }
}
