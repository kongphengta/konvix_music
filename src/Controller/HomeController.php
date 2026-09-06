<?php

namespace App\Controller;

use App\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SongRepository $songRepository, Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12;
        $totalSongs = $songRepository->count([]);
        $totalPages = max(1, (int) ceil($totalSongs / $limit));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $limit;

        $latestSongs = $songRepository->findBy([], ['id' => 'DESC'], $limit, $offset);

        $latestSongs = array_values(array_filter(
            $latestSongs,
            static fn ($song) => $song !== null && $song->getTitle() !== null && $song->getSlug() !== null
        ));

        return $this->render('home/index.html.twig', [
            'latestSongs' => $latestSongs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalSongs' => $totalSongs,
        ]);
    }
}
