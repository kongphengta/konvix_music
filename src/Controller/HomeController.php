<?php

namespace App\Controller;

use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tracks = $entityManager->getRepository(Track::class)->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('home/index.html.twig', [
            'tracks' => $tracks,
        ]);
    }
}
