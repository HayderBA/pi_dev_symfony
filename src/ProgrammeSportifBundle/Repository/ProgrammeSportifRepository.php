<?php

namespace App\ProgrammeSportifBundle\Repository;

use App\Entity\Utilisateur;
use App\ProgrammeSportifBundle\Entity\ProgrammeSportif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProgrammeSportif>
 */
final class ProgrammeSportifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgrammeSportif::class);
    }

    /**
     * @return ProgrammeSportif[]
     */
    public function findLatest(int $limit = 12): array
    {
        return $this->createQueryBuilder('programme')
            ->orderBy('programme.createdAt', 'DESC')
            ->addOrderBy('programme.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLatestByUser(Utilisateur $user): ?ProgrammeSportif
    {
        return $this->createQueryBuilder('programme')
            ->andWhere('programme.user = :user')
            ->setParameter('user', $user)
            ->orderBy('programme.createdAt', 'DESC')
            ->addOrderBy('programme.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
