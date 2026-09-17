<?php

namespace App\Tests\Entity;

use App\Entity\Artist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArtistEntityTest extends KernelTestCase
{
    public function testArtistCanBePersisted(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $artist = new Artist();
        $artist->setSlug('luna-kairo-' . uniqid());
        $artist->setName('Luna Kairo');
        $artist->setGenre('Synthwave / dream pop');
        $artist->setCity('Paris');
        $artist->setFollowers('42K');
        $artist->setBio('A midnight voice with a cinematic pulse.');
        $artist->setColor('violet');

        $entityManager->persist($artist);
        $entityManager->flush();

        $this->assertNotNull($artist->getId());
    }
}
