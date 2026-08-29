<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SongRepository;


final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SongRepository $songRepository): Response
    {
        $latestSongs = $songRepository->findBy([], ['id' => 'DESC'], 6);

        $latestSongs = array_values(array_filter(
            $latestSongs,
            static fn ($song) => $song !== null && $song->getTitle() !== null && $song->getSlug() !== null
        ));

        return $this->render('home/index.html.twig', [
            'latestSongs' => $latestSongs,
        ]);
    }
}
