<?php
// src/Repository/EvenementRepository.php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    public function searchEvents(string $keyword): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.titre LIKE :keyword')
            ->orWhere('e.description LIKE :keyword')
            ->orWhere('e.localisation LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.date >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findEventsThisMonth(): array
    {
        $now = new \DateTime();
        $firstDay = new \DateTime($now->format('Y-m-01'));
        $lastDay = new \DateTime($now->format('Y-m-t'));

        return $this->createQueryBuilder('e')
            ->where('e.date BETWEEN :start AND :end')
            ->setParameter('start', $firstDay)
            ->setParameter('end', $lastDay)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}