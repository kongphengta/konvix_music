<?php

namespace App\Tests\Controller;

use App\Controller\HomeController;
use App\Entity\Track;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class HomeControllerTest extends TestCase
{
    public function testIndexRendersFallbackArtistNameWhenArtistProfileIsMissing(): void
    {
        $user = new User();
        $user->setFirstName('Luna');
        $user->setLastName('Wave');
        $user->setUsername('luna-wave');
        $user->setEmail('luna@example.com');
        $user->setPassword('hashed-password');
        $user->setRoles(['ROLE_ARTIST']);

        $track = new Track();
        $track->setArtist($user);
        $track->setTitle('Midnight Echo');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with([], ['createdAt' => 'DESC'], 5)
            ->willReturn([$track]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')
            ->with(Track::class)
            ->willReturn($repository);

        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with(
                'home/index.html.twig',
                [
                    'tracks' => [[
                        'title' => 'Midnight Echo',
                        'audioFileName' => null,
                        'coverFileName' => null,
                        'artistName' => 'Luna Wave',
                        'artistCity' => '',
                        'createdAt' => $track->getCreatedAt(),
                    ]],
                ]
            )
            ->willReturn('<html><body>ok</body></html>');

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('twig')->willReturn(true);
        $container->method('get')->with('twig')->willReturn($twig);

        $controller = new HomeController();
        $controller->setContainer($container);

        $response = $controller->index($entityManager);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('<html><body>ok</body></html>', $response->getContent());
    }
}
