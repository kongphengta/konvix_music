<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MusicControllerTest extends WebTestCase
{
    public function testSongListRoutesAreAvailable(): void
    {
        $router = static::getContainer()->get('router');

        $this->assertNotNull($router->getRouteCollection()->get('app_song_index'));
        $this->assertNotNull($router->getRouteCollection()->get('app_song_list'));
        $this->assertSame('/songs/list', $router->generate('app_song_list'));
    }

    public function testAlbumUnknownReturnsNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/album/inexistant-slug');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testLoginPageContainsForgotPasswordLink(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a', 'Mot de passe oublié ?');
        $this->assertSelectorExists('a[href="/forgot-password"]');
    }

    public function testSongStoresPublicationDateAndListenCount(): void
    {
        $song = new \App\Entity\Song();
        $publishedAt = new \DateTimeImmutable('2026-01-15 12:00:00');

        $song->setPublishedAt($publishedAt);
        $song->setListenCount(1284);

        $this->assertSame($publishedAt, $song->getPublishedAt());
        $this->assertSame(1284, $song->getListenCount());
    }
}
