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
        /** @var User $user */
        $user          = $this->getUser();
        $artistProfile = $user->getArtistProfile();

        return $this->render('artist/dashboard.html.twig', [
            'artist' => $artistProfile,
            'songs'  => $artistProfile ? $artistProfile->getSongs() : [],
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
