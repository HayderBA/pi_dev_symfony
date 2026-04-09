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
     * Recherche (nom, ville, adresse, email), filtre statut / ville, tri.
     *
     * @return Cabinet[]
     */
    public function findFiltered(
        ?string $search,
        ?string $status,
        ?string $ville,
        string $sort = 'nom',
        string $direction = 'asc'
    ): array {
        $qb = $this->createQueryBuilder('c');

        if ($search !== null && $search !== '') {
            $q = '%' . mb_strtolower($search) . '%';
            $qb->andWhere($qb->expr()->orX(
                'LOWER(c.nomcabinet) LIKE :q',
                'LOWER(c.ville) LIKE :q',
                'LOWER(c.adresse) LIKE :q',
                'LOWER(c.email) LIKE :q'
            ))->setParameter('q', $q);
        }

        if ($status !== null && $status !== '') {
            $qb->andWhere('c.status = :status')->setParameter('status', $status);
        }

        if ($ville !== null && $ville !== '') {
            $qb->andWhere('LOWER(c.ville) LIKE :ville')
                ->setParameter('ville', '%' . mb_strtolower($ville) . '%');
        }

        $col = self::SORT_FIELDS[$sort] ?? self::SORT_FIELDS['nom'];
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy($col, $dir)->addOrderBy('c.idCabinet', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return list<string>
     */
    public function findDistinctVilles(): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.ville')
            ->distinct()
            ->orderBy('c.ville', 'ASC')
            ->getQuery()
            ->getScalarResult();

        $villes = array_unique(array_map(
            static fn (array $row) => $row['ville'] ?? null,
            $result
        ));

        return array_values(array_filter(
            $villes,
            static fn ($v) => $v !== null && $v !== ''
        ));
    }

    /**
     * Statistiques globales cabinets (totaux par statut).
     *
     * @return array{total: int, by_status: array<string, int>}
     */
    public function getStatistics(): array
    {
        $total = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.idCabinet)')
            ->getQuery()
            ->getSingleScalarResult();

        $raw = $this->createQueryBuilder('c')
            ->select('c.status AS st, COUNT(c.idCabinet) AS cnt')
            ->groupBy('c.status')
            ->getQuery()
            ->getArrayResult();

        $byStatus = [];
        foreach ($raw as $row) {
            $byStatus[(string) $row['st']] = (int) $row['cnt'];
        }

        return [
            'total' => $total,
            'by_status' => $byStatus,
        ];
    }
}
