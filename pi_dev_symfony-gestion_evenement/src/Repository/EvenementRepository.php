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

    public function findEventsBetweenDates(\DateTime $startDate, \DateTime $endDate): array
    {
        $start = (clone $startDate)->setTime(0, 0, 0);
        $end = (clone $endDate)->setTime(23, 59, 59);

        return $this->createQueryBuilder('e')
            ->where('e.date >= :start AND e.date <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}