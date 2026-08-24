<?php

namespace App\Repository;

use App\Entity\ArtistProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArtistProfile>
 */
class ArtistProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtistProfile::class);
    }

    public function findApproved(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isApproved = true')
            ->orderBy('a.stageName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
