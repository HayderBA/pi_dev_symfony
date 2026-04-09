<?php
// src/Repository/ReservationRepository.php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.utilisateurId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByEvent(int $eventId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.evenement = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}