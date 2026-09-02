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
}
