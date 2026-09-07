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

    public function testStreamFileRequestDoesNotIncreaseListenCount(): void
    {
        $song = new \App\Entity\Song();
        $song->setTitle('Test stream');
        $song->setSlug('test-stream');
        $song->setFilename('test-stream.mp3');
        $song->setListenCount(7);

        $repository = $this->createMock(\App\Repository\SongRepository::class);
        $repository->method('findOneBy')->with(['slug' => 'test-stream'])->willReturn($song);

        $projectDir = sys_get_temp_dir() . '/konvix_music_stream_test';
        @mkdir($projectDir . '/public/uploads/songs', 0777, true);
        file_put_contents($projectDir . '/public/uploads/songs/test-stream.mp3', 'fake audio');

        $controller = new class($projectDir) extends \App\Controller\StreamController {
            public function __construct(private readonly string $projectDir) {}

            public function getParameter(string $name): \UnitEnum|array|string|int|float|bool|null
            {
                if ($name === 'kernel.project_dir') {
                    return $this->projectDir;
                }

                return parent::getParameter($name);
            }
        };

        $response = $controller->play('test-stream', $repository);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(7, $song->getListenCount());
    }
}
