<?php

namespace App\Repository;

use App\Entity\Psychologue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Psychologue>
 */
class PsychologueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Psychologue::class);
    }

    /**
     * @return Psychologue[]
     */
    public function findFiltered(?string $search, ?int $cabinetId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.cabinet', 'c')->addSelect('c')
            ->leftJoin('p.user', 'u')->addSelect('u');

        if ($search) {
            $q = '%' . mb_strtolower($search) . '%';
            $qb->andWhere('LOWER(p.nom) LIKE :q OR LOWER(p.prenom) LIKE :q OR LOWER(p.specialite) LIKE :q OR LOWER(c.nomcabinet) LIKE :q')
                ->setParameter('q', $q);
        }

        if ($cabinetId) {
            $qb->andWhere('c.idCabinet = :cabinetId')->setParameter('cabinetId', $cabinetId);
        }

        return $qb->orderBy('p.nom', 'ASC')->addOrderBy('p.prenom', 'ASC')->getQuery()->getResult();
    }

    /**
     * @return array{total:int,linked_users:int,avg_tarif:float}
     */
    public function getStatistics(): array
    {
        $total = (int) $this->createQueryBuilder('p')->select('COUNT(p.idPsychologue)')->getQuery()->getSingleScalarResult();
        $linked = (int) $this->createQueryBuilder('p')->select('COUNT(p.idPsychologue)')->andWhere('p.user IS NOT NULL')->getQuery()->getSingleScalarResult();
        $avg = (float) ($this->createQueryBuilder('p')->select('AVG(p.tarif)')->getQuery()->getSingleScalarResult() ?? 0);

        return ['total' => $total, 'linked_users' => $linked, 'avg_tarif' => round($avg, 2)];
    }

    /**
     * @return Psychologue[]
     */
    public function findByCabinetId(int $cabinetId): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')->addSelect('u')
            ->andWhere('p.cabinet = :cabinetId')
            ->setParameter('cabinetId', $cabinetId)
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
