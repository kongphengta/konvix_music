<?php

namespace App\Tests\Controller;

use App\Controller\Admin\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\ControllerMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
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
        $this->assertSelectorExists('a[href="/forgot-password"]');
        $this->assertSelectorTextContains('a[href="/forgot-password"]', 'Mot de passe oublié ?');
    }

    public function testStreamRouteExistsForPlayerPlayback(): void
    {
        $router = static::getContainer()->get('router');

        $this->assertNotNull($router->getRouteCollection()->get('app_stream_song'));
        $this->assertSame('/stream/midnight-rodeo-king', $router->generate('app_stream_song', ['slug' => 'midnight-rodeo-king']));
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

    public function testArtistUserFullNameFallsBackWhenNamesAreMissing(): void
    {
        $user = new \App\Entity\User();

        $firstNameProperty = new \ReflectionProperty(\App\Entity\User::class, 'firstName');
        $firstNameProperty->setAccessible(true);
        $firstNameProperty->setValue($user, null);

        $lastNameProperty = new \ReflectionProperty(\App\Entity\User::class, 'lastName');
        $lastNameProperty->setAccessible(true);
        $lastNameProperty->setValue($user, null);

        $this->assertSame('Artiste', $user->getFullName());
    }

    public function testUserCanBeConvertedToStringForEasyAdminAssociationFields(): void
    {
        $user = new \App\Entity\User();
        $user->setEmail('artist@example.com');

        $this->assertSame('artist@example.com', (string) $user);
    }

    public function testUserWithNullRolesDoesNotCrashWhenCheckingArtistStatus(): void
    {
        $user = new \App\Entity\User();

        $rolesProperty = new \ReflectionProperty(\App\Entity\User::class, 'roles');
        $rolesProperty->setAccessible(true);
        $rolesProperty->setValue($user, null);

        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertFalse($user->isArtist());
    }

    public function testAdminDashboardMenuItemsSpecifyCrudAction(): void
    {
        $controller = new DashboardController();

        foreach (iterator_to_array($controller->configureMenuItems()) as $item) {
            if (!$item instanceof ControllerMenuItem) {
                continue;
            }

            $routeParameters = (new \ReflectionProperty($item, 'dto'))->getValue($item)->getRouteParameters();
            $this->assertNotNull($routeParameters[EA::CRUD_ACTION] ?? null);
            $this->assertSame(Action::INDEX, $routeParameters[EA::CRUD_ACTION]);
        }
    }

    public function testStreamFileRequestIncrementsListenCountOncePerSession(): void
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

        $requestStack = new \Symfony\Component\HttpFoundation\RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->setSession(new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage()));
        $requestStack->push($request);

        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        $response = $controller->play('test-stream', $repository, $requestStack, $entityManager);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(8, $song->getListenCount());
    }

    public function testStreamFileRequestDoesNotIncreaseListenCountWithoutPersistenceContext(): void
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
