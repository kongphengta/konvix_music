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

        $normalizedTracks = [];
        foreach ($tracks as $track) {
            $artist = $track->getArtist();
            $artistProfile = $artist?->getArtistProfile();

            $normalizedTracks[] = [
                'title' => $track->getTitle(),
                'audioFileName' => $track->getAudioFileName(),
                'coverFileName' => $track->getCoverImageName(),
                'artistName' => $this->resolveArtistName($artist, $artistProfile),
                'artistCity' => $artistProfile?->getCity() ?? '',
                'createdAt' => $track->getCreatedAt(),
            ];
        }

        return $this->render('home/index.html.twig', [
            'tracks' => $normalizedTracks,
        ]);
    }

    private function resolveArtistName(?\App\Entity\User $artist, ?\App\Entity\ArtistProfile $artistProfile): string
    {
        if ($artistProfile !== null && $artistProfile->getArtistName()) {
            return $artistProfile->getArtistName();
        }

        if ($artist === null) {
            return 'Artiste';
        }

        $firstName = trim((string) $artist->getFirstName());
        $lastName = trim((string) $artist->getLastName());

        if ($firstName !== '' && $lastName !== '') {
            return $firstName . ' ' . $lastName;
        }

        if ($firstName !== '') {
            return $firstName;
        }

        if ($lastName !== '') {
            return $lastName;
        }

        return 'Artiste';
    }
}
