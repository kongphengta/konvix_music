<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ArtistProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArtistController extends AbstractController
{
    #[Route('/artist/dashboard', name: 'app_artist_dashboard')]
    #[IsGranted('ROLE_ARTIST')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à votre espace artiste.');
        }

        $artistProfile = $user->getArtistProfile();

        if (!$artistProfile) {
            $this->addFlash('warning', 'Votre profil artiste est incomplet. Complétez-le pour accéder au tableau de bord.');

            return $this->render('artist/dashboard.html.twig', [
                'artist' => null,
                'songs'  => [],
            ]);
        }

        return $this->render('artist/dashboard.html.twig', [
            'artist' => $artistProfile,
            'songs'  => $artistProfile->getSongs(),
        ]);
    }

    #[Route('/artist/{slug}', name: 'app_artist_public_profile')]
    public function publicProfile(string $slug, ArtistProfileRepository $repo): Response
    {
        $artist = $repo->findOneBy(['slug' => $slug, 'isApproved' => true]);

        if (!$artist) {
            throw $this->createNotFoundException('Artiste introuvable.');
        }

        return $this->render('artist/profile.html.twig', [
            'artist' => $artist,
        ]);
    }
}
