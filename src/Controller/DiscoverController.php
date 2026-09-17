<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoverController extends AbstractController
{
    #[Route('/discover', name: 'app_discover')]
    public function index(): Response
    {
        $categories = [
            ['title' => 'Synthwave', 'count' => '24 mixes', 'color' => 'violet'],
            ['title' => 'Lo-fi', 'count' => '18 mixes', 'color' => 'cyan'],
            ['title' => 'Dance', 'count' => '31 mixes', 'color' => 'pink'],
            ['title' => 'Soul', 'count' => '15 mixes', 'color' => 'gold'],
        ];

        $highlights = [
            ['title' => 'Nova Glow', 'artist' => 'Luna Kairo', 'duration' => '26 min', 'color' => 'violet'],
            ['title' => 'Blue Line', 'artist' => 'Noah Vale', 'duration' => '21 min', 'color' => 'cyan'],
            ['title' => 'Palm Echo', 'artist' => 'Sora Mint', 'duration' => '19 min', 'color' => 'pink'],
            ['title' => 'Electric Bloom', 'artist' => 'Mila Greys', 'duration' => '24 min', 'color' => 'rose'],
        ];

        $editorials = [
            ['title' => 'Les artistes à surveiller ce mois-ci', 'text' => 'Des voix plus nettes, des grooves plus profonds, des productions qui traversent la nuit.'],
            ['title' => 'Les albums qui donnent envie de rester en boucle', 'text' => 'Des morceaux conçus pour l’écoute immersive, le mouvement lent et le souvenir durable.'],
            ['title' => 'Comment mieux structurer sa session musicale', 'text' => 'Créer une ambiance, varier les textures et laisser les morceaux respirer pour une vraie immersion.'],
        ];

        return $this->render('discover/index.html.twig', [
            'categories' => $categories,
            'highlights' => $highlights,
            'editorials' => $editorials,
        ]);
    }
}
