<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    #[Route('/albums', name: 'app_albums')]
    public function index(AlbumRepository $repo): Response
    {
        return $this->render('album/index.html.twig', [
            'albums' => $repo->findAll(),
        ]);
    }

    #[Route('/album/{slug}', name: 'app_album_show')]
    public function show(AlbumRepository $repo, string $slug): Response
    {
        $album = $repo->findOneBy(['slug' => $slug]);

        return $this->render('album/show.html.twig', [
            'album' => $album,
            'songs' => $album->getSongs(),
        ]);
    }
}
