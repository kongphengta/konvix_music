<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ArtistDashboardController extends AbstractController
{
    #[Route('/artist/dashboard', name: 'app_artist_dashboard')]
    #[IsGranted('ROLE_ARTISTE')]
    public function dashboard(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $artists = $doctrine->getRepository(Artist::class)->findBy([
            'user' => $user,
        ]);

        return $this->render('artist/dashboard.html.twig', [
            'artists' => $artists,
        ]);
    }

    #[Route('/artist/profile/new', name: 'app_artist_profile_new')]
    #[IsGranted('ROLE_ARTISTE')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $artist = new Artist();
        $artist->setUser($this->getUser());

        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artist->setIsPublished(false);
            $doctrine->getManager()->persist($artist);
            $doctrine->getManager()->flush();

            $this->addFlash('success', 'Votre profil artiste a été enregistré et est en attente de validation.');

            return $this->redirectToRoute('app_artist_dashboard');
        }

        return $this->render('artist/profile_new.html.twig', [
            'form' => $form,
        ]);
    }
}
