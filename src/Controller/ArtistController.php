<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArtistController extends AbstractController
{
    #[Route('/artists', name: 'app_artists')]
    public function index(ArtistRepository $artistRepository): Response
    {
        $artists = $artistRepository->findBy(['isPublished' => true], ['id' => 'DESC']);

        return $this->render('artists/index.html.twig', [
            'artists' => $artists,
        ]);
    }
}
