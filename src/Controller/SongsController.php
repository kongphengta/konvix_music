<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class SongsController extends AbstractController
{
    private array $songs = [
        [
            'title' => 'Hand In Mine',
            'slug' => 'hand-in-mine',
            'category' => 'country',
            'album' => 'konvix-country-vol1',
            'audioUrl' => '/uploads/songs/Hand In Mine/hand-in-mine.mp3',
            'description' => 'Une chanson country moderne dance honkytonk.',
            'image' => '/images/HandInMine.jpg',
        ],
        [
            'title' => 'A Hundred Years With You',
            'slug' => 'a-hundred-years-with-you',
            'category' => 'country',
            'album' => 'konvix-country-vol1',
            'audioUrl' => '/uploads/songs/a-hundred-years-with-you.mp3',
            'description' => 'Un morceau romantique et émouvant.',
        ],
        [
            'title' => 'Laos Memory',
            'slug' => 'laos-memory',
            'category' => 'nostalgie',
            'album' => 'souvenirs-du-laos',
            'audioUrl' => '/uploads/songs/laos-memory.mp3',
            'description' => 'Une balade nostalgique inspirée du Laos.',
        ],
        [
            'title' => 'Konvix Dance',
            'slug' => 'konvix-dance',
            'category' => 'dance',
            'album' => 'konvix-dance-vol1',
            'audioUrl' => '/uploads/songs/konvix-dance.mp3',
            'description' => 'Un morceau dance moderne pour faire bouger la piste.',
        ],
        [
            'title' => 'Country Road Again',
            'slug' => 'country-road-again',
            'category' => 'country',
            'album' => 'konvix-country-vol1',
            'audioUrl' => '/uploads/songs/country-road-again.mp3',
            'description' => 'Une chanson country classique avec guitare acoustique.',
        ],
        [
            'title' => 'Blue Sky Honkytonk',
            'slug' => 'blue-sky-honkytonk',
            'category' => 'honkytonk',
            'album' => 'konvix-country-vol1',
            'audioUrl' => '/uploads/songs/blue-sky-honkytonk.mp3',
            'description' => 'Un honkytonk rapide et joyeux.',
        ],
        [
            'title' => 'Dancefloor Fever',
            'slug' => 'dancefloor-fever',
            'category' => 'dance',
            'album' => 'konvix-dance-vol1',
            'audioUrl' => '/uploads/songs/dancefloor-fever.mp3',
            'description' => 'Un titre dance énergique pour les soirées.',
        ],
        [
            'title' => 'Memories of Mekong',
            'slug' => 'memories-of-mekong',
            'category' => 'nostalgie',
            'album' => 'souvenirs-du-laos',
            'audioUrl' => '/uploads/songs/memories-of-mekong.mp3',
            'description' => 'Un morceau doux et nostalgique inspiré du Mékong.',
        ],
        [
            'title' => 'Paksane Sunset',
            'slug' => 'paksane-sunset',
            'category' => 'nostalgie',
            'album' => 'souvenirs-du-laos',
            'audioUrl' => '/uploads/songs/paksane-sunset.mp3',
            'description' => 'Une ambiance calme et émotionnelle.',
        ],
        [
            'title' => 'Electric Cowboy',
            'slug' => 'electric-cowboy',
            'category' => 'country',
            'album' => 'konvix-country-vol1',
            'audioUrl' => '/uploads/songs/electric-cowboy.mp3',
            'description' => 'Country moderne avec guitare électrique.',
        ],
        [
            'title' => 'Night Dance Lights',
            'slug' => 'night-dance-lights',
            'category' => 'dance',
            'album' => 'konvix-dance-vol1',
            'audioUrl' => '/uploads/songs/night-dance-lights.mp3',
            'description' => 'Un morceau dance lumineux et rythmé.',
        ],
        [
            'title' => 'Honkytonk Spirit',
            'slug' => 'honkytonk-spirit',
            'category' => 'honkytonk',
            'album' => 'konvix-country-vol1',
            'audioUrl' => '/uploads/songs/honkytonk-spirit.mp3',
            'description' => 'Un honkytonk authentique avec piano et steel guitar.',
        ],
    ];

    #[Route('/songs', name: 'app_songs')]
    public function index(Request $request): Response
    {
        // Ton tableau existant
        $songs = $this->songs;

        // --- AJOUT PAGINATION ---
        $perPage = 6; // nombre de chansons par page
        $page = max(1, (int) $request->query->get('page', 1));
        $total = count($songs);
        $totalPages = (int) ceil($total / $perPage);

        $offset = ($page - 1) * $perPage;
        $songsPage = array_slice($songs, $offset, $perPage);
        // --- FIN AJOUT PAGINATION ---

        return $this->render('songs/index.html.twig', [
            'songs' => $songsPage,   // <-- on envoie seulement la page courante
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/songs/search', name: 'app_songs_search')]
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $filtered = [];

        if ($query !== '') {
            foreach ($this->songs as $song) {
                $haystack = strtolower(
                    ($song['title'] ?? '') . ' ' .
                        ($song['category'] ?? '') . ' ' .
                        ($song['album'] ?? '')
                );

                if (str_contains($haystack, strtolower($query))) {
                    $filtered[] = $song;
                }
            }
        }

        return $this->render('songs/search.html.twig', [
            'query' => $query,
            'songs' => $filtered,
        ]);
    }

    #[Route('/songs/{slug}', name: 'app_song_show')]
    public function show(string $slug): Response
    {
        foreach ($this->songs as $song) {
            if (isset($song['slug']) && $song['slug'] === $slug) {
                return $this->render('songs/show.html.twig', [
                    'song' => $song,
                ]);
            }
        }

        throw $this->createNotFoundException("La chanson demandée n'existe pas.");
    }

    #[Route('/songs/category/{category}', name: 'app_songs_category')]
    public function category(string $category): Response
    {
        $filtered = [];

        foreach ($this->songs as $song) {
            if (($song['category'] ?? null) === $category) {
                $filtered[] = $song;
            }
        }

        return $this->render('songs/category.html.twig', [
            'category' => $category,
            'songs' => $filtered,
        ]);
    }

    #[Route('/songs/album/{album}', name: 'app_songs_album')]
    public function album(string $album): Response
    {
        $filtered = [];

        foreach ($this->songs as $song) {
            if (($song['album'] ?? null) === $album) {
                $filtered[] = $song;
            }
        }

        return $this->render('songs/album.html.twig', [
            'album' => $album,
            'songs' => $filtered,
        ]);
    }
}
