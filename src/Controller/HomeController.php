<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'featured' => [
                ['title' => 'Midnight Echo', 'length' => '3:42'],
                ['title' => 'Neon Skyline', 'length' => '4:18'],
                ['title' => 'Velvet Engine', 'length' => '5:01'],
            ],
            'highlights' => [
                [
                    'icon' => '🎧',
                    'title' => 'Sélection curatée',
                    'text' => 'Découvrez des morceaux choisis pour accompagner vos soirées et vos sessions créatives.',
                ],
                [
                    'icon' => '🌙',
                    'title' => 'Ambiance nocturne',
                    'text' => 'Des sons doux, immersifs et inspirants pour une vibe plus profonde.',
                ],
                [
                    'icon' => '🔥',
                    'title' => 'Nova playlists',
                    'text' => 'Des playlists qui évoluent en fonction des tendances de la scène musicale indépendante.',
                ],
            ],
        ]);
    }
}
