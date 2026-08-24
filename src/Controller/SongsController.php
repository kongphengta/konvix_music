<?php

namespace App\Controller;

use App\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;


class SongsController extends AbstractController
{
    #[Route('/songs', name: 'app_song_index')]
    public function index(SongRepository $songRepository): Response
    {
        $songs = $songRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('songs/index.html.twig', [
            'songs' => $songs,
        ]);
    }

    #[Route('/songs/list', name: 'app_song_list')]
    public function list(SongRepository $songRepository): Response
    {
        return $this->index($songRepository);
    }

    #[Route('/songs/{slug}', name: 'app_song_show')]
    public function show(string $slug, SongRepository $songRepository): Response
    {
        $song = $songRepository->findOneBy(['slug' => $slug]);

        if (!$song) {
            return $this->render('songs/not_found.html.twig', [
                'slug' => $slug,
            ]);
        }

        return $this->render('songs/show.html.twig', [
            'song' => $song,
        ]);
    }

    #[Route('/songs/album/{album}', name: 'app_song_album')]
    public function album(string $album, SongRepository $songRepository): Response
    {
        $songs = $songRepository->findBy(['album' => $album], ['createdAt' => 'DESC']);

        return $this->render('songs/album.html.twig', [
            'album' => $album,
            'songs' => $songs,
        ]);
    }

    #[Route('/songs/category/{category}', name: 'app_song_category')]
    public function category(string $category, SongRepository $songRepository): Response
    {
        $songs = $songRepository->findBy(['category' => $category], ['createdAt' => 'DESC']);

        return $this->render('songs/category.html.twig', [
            'category' => $category,
            'songs' => $songs,
        ]);
    }

    #[Route('/songs/new', name: 'app_song_new')]
    public function new(): Response
    {
        return $this->render('songs/new.html.twig');
    }

    #[Route('/songs/search', name: 'app_song_search')]

    public function search(Request $request, SongRepository $songRepository): Response
    {
        $query = trim($request->query->get('q', ''));

        $songs = [];

        if ($query !== '') {
            $songs = $songRepository->createQueryBuilder('s')
                ->where('s.title LIKE :q')
                ->orWhere('s.album LIKE :q')
                ->orWhere('s.category LIKE :q')
                ->orWhere('s.description LIKE :q')
                ->setParameter('q', '%' . $query . '%')
                ->orderBy('s.createdAt', 'DESC')

                ->getQuery()
                ->getResult();
        }

        return $this->render('songs/search.html.twig', [
            'query' => $query,
            'songs' => $songs,
        ]);
    }
}
