<?php

namespace App\Repository;

use App\Entity\Cabinet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cabinet>
 */
class CabinetRepository extends ServiceEntityRepository
{
    private const SORT_FIELDS = [
        'nom' => 'c.nomcabinet',
        'ville' => 'c.ville',
        'status' => 'c.status',
        'id' => 'c.idCabinet',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cabinet::class);
    }

    /**
     * @return Cabinet[]
     */
    public function findFiltered(?string $search, ?string $status, ?string $ville, string $sort = 'nom', string $direction = 'asc'): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $q = '%' . mb_strtolower($search) . '%';
            $qb->andWhere('LOWER(c.nomcabinet) LIKE :q OR LOWER(c.ville) LIKE :q OR LOWER(c.adresse) LIKE :q OR LOWER(c.email) LIKE :q')
                ->setParameter('q', $q);
        }

        if ($status) {
            $qb->andWhere('c.status = :status')->setParameter('status', $status);
        }

        if ($ville) {
            $qb->andWhere('LOWER(c.ville) LIKE :ville')->setParameter('ville', '%' . mb_strtolower($ville) . '%');
        }

        $sortField = self::SORT_FIELDS[$sort] ?? self::SORT_FIELDS['nom'];
        $dir = 'DESC' === strtoupper($direction) ? 'DESC' : 'ASC';

        return $qb->orderBy($sortField, $dir)->addOrderBy('c.idCabinet', 'ASC')->getQuery()->getResult();
    }

    /**
     * @return list<string>
     */
    public function findDistinctVilles(): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c.ville')
            ->distinct()
            ->orderBy('c.ville', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_values(array_filter(array_unique(array_map(static fn (array $row) => $row['ville'] ?? '', $rows))));
    }

    /**
     * @return array{total:int,by_status:array<string,int>}
     */
    public function getStatistics(): array
    {
        $total = (int) $this->createQueryBuilder('c')->select('COUNT(c.idCabinet)')->getQuery()->getSingleScalarResult();
        $raw = $this->createQueryBuilder('c')->select('c.status AS st, COUNT(c.idCabinet) AS cnt')->groupBy('c.status')->getQuery()->getArrayResult();

        $byStatus = [];
        foreach ($raw as $row) {
            $byStatus[(string) $row['st']] = (int) $row['cnt'];
        }

        return ['total' => $total, 'by_status' => $byStatus];
    }
}
