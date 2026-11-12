<?php

namespace App\Tests\Controller;

use App\Controller\ArtistTrackController;
use App\Entity\ArtistProfile;
use App\Entity\Track;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class ArtistPublicPageTest extends TestCase
{
    public function testPublicProfileLoadsArtistDetailsAndTracks(): void
    {
        $user = new User();
        $user->setFirstName('Luna');
        $user->setLastName('Wave');
        $user->setUsername('luna-wave');
        $user->setEmail('luna@example.com');
        $user->setPassword('hashed-password');
        $user->setRoles(['ROLE_ARTIST']);
        $user->setIsVerified(true);

        $profile = new ArtistProfile();
        $profile->setUser($user);
        $profile->setArtistName('Luna Wave');
        $profile->setSlug('luna-wave');
        $profile->setBio('Art pop et énergie pure.');
        $profile->setCity('Paris');
        $profile->setCountry('France');

        $track = new Track();
        $track->setArtist($user);
        $track->setTitle('Midnight Echo');

        $profileRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $profileRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'luna-wave'])
            ->willReturn($profile);

        $trackRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $trackRepository->expects($this->once())
            ->method('findBy')
            ->with(['artist' => $user], ['createdAt' => 'DESC'])
            ->willReturn([$track]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')
            ->willReturnMap([
                [ArtistProfile::class, $profileRepository],
                [Track::class, $trackRepository],
            ]);

        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with(
                'artist/public_profile.html.twig',
                [
                    'profile' => $profile,
                    'tracks' => [$track],
                ]
            )
            ->willReturn('<html><body>Luna Wave Midnight Echo</body></html>');

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('twig')->willReturn(true);
        $container->method('get')->with('twig')->willReturn($twig);

        $controller = new ArtistTrackController();
        $controller->setContainer($container);

        $response = $controller->publicProfile('luna-wave', $entityManager);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('Luna Wave', $response->getContent());
        $this->assertStringContainsString('Midnight Echo', $response->getContent());
    }
}
