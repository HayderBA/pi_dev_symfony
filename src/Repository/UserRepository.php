<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('LOWER(u.role) = :role')
            ->setParameter('role', mb_strtolower($role))
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAnyPatient(): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('LOWER(u.role) = :role')
            ->setParameter('role', 'patient')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAnyMedecin(): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('LOWER(u.role) = :role')
            ->setParameter('role', 'medecin')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
