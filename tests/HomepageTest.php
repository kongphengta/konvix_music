<?php

namespace App\Tests;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomepageTest extends WebTestCase
{
    public function testHomepageIsAccessible(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Konvix Music');
        $this->assertSelectorTextContains('h2', 'La vibe du moment');
    }

    public function testPlaylistsPageIsAccessible(): void
    {
        $client = static::createClient();

        $client->request('GET', '/playlists');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Playlists');
    }

    public function testDiscoverPageIsAccessible(): void
    {
        $client = static::createClient();

        $client->request('GET', '/discover');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Découvrir');
    }

    public function testArtistDetailPageIsAccessible(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $artist = new \App\Entity\Artist();
        $artist->setSlug('luna-kairo-' . uniqid());
        $artist->setName('Luna Kairo');
        $artist->setGenre('Synthwave / dream pop');
        $artist->setCity('Paris');
        $artist->setFollowers('42K');
        $artist->setBio('Une voix qui flotte entre le ciel nocturne et la chaleur des dancefloors.');
        $artist->setColor('violet');
        $artist->setIsPublished(true);

        $entityManager->persist($artist);
        $entityManager->flush();

        $client->request('GET', '/artists/' . $artist->getSlug());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Luna Kairo');
    }

    public function testArtistsPageDisplaysPersistedArtist(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $slug = 'nova-echo-' . uniqid();
        $artist = new \App\Entity\Artist();
        $artist->setSlug($slug);
        $artist->setName('Nova Echo');
        $artist->setGenre('Electronic pop');
        $artist->setCity('Paris');
        $artist->setFollowers('12K');
        $artist->setBio('Un vrai artiste ajouté depuis la base de données.');
        $artist->setColor('violet');
        $artist->setIsPublished(true);

        $entityManager->persist($artist);
        $entityManager->flush();

        $client->request('GET', '/artists');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Artistes à suivre');
        $this->assertSelectorTextContains('body', 'Nova Echo');

        $client->request('GET', '/artists/' . $slug);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Nova Echo');
    }
}
