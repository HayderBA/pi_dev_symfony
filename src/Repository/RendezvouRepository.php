<?php

namespace App\Repository;

use App\Entity\Rendezvou;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rendezvou>
 */
class RendezvouRepository extends ServiceEntityRepository
{
    private const SORT_FIELDS = [
        'date' => 'r.dateRdv',
        'id' => 'r.idRdv',
        'statut' => 'r.statut',
        'heure' => 'r.heure',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rendezvou::class);
    }

    /**
     * Recherche patient (nom, prénom, email), filtre statut et plage de dates, tri.
     *
     * @return Rendezvou[]
     */
    public function findFiltered(
        ?string $search,
        ?string $statut,
        ?\DateTimeInterface $dateFrom,
        ?\DateTimeInterface $dateTo,
        string $sort = 'date',
        string $direction = 'desc'
    ): array {
        $qb = $this->createQueryBuilder('r');

        if ($search !== null && $search !== '') {
            $q = '%' . mb_strtolower($search) . '%';
            $qb->andWhere($qb->expr()->orX(
                'LOWER(r.nom_patient) LIKE :q',
                'LOWER(r.prenom_patient) LIKE :q',
                'LOWER(r.email_patient) LIKE :q'
            ))->setParameter('q', $q);
        }

        if ($statut !== null && $statut !== '') {
            $qb->andWhere('r.statut = :statut')->setParameter('statut', $statut);
        }

        if ($dateFrom !== null) {
            $qb->andWhere('r.dateRdv >= :df')->setParameter('df', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb->andWhere('r.dateRdv <= :dt')->setParameter('dt', $dateTo);
        }

        $col = self::SORT_FIELDS[$sort] ?? self::SORT_FIELDS['date'];
        $dir = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $qb->orderBy($col, $dir)->addOrderBy('r.heure', $dir)->addOrderBy('r.idRdv', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array{
     *   total: int,
     *   by_statut: array<string, int>,
     *   a_venir: int,
     *   aujourd_hui: int
     * }
     */
    public function getStatistics(): array
    {
        $total = (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.idRdv)')
            ->getQuery()
            ->getSingleScalarResult();

        $raw = $this->createQueryBuilder('r')
            ->select('r.statut AS st, COUNT(r.idRdv) AS cnt')
            ->groupBy('r.statut')
            ->getQuery()
            ->getArrayResult();

        $byStatut = [];
        foreach ($raw as $row) {
            $byStatut[(string) $row['st']] = (int) $row['cnt'];
        }

        $today = new \DateTimeImmutable('today');
        $tomorrow = $today->modify('+1 day');

        $aujourdhui = (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.idRdv)')
            ->where('r.dateRdv >= :t0 AND r.dateRdv < :t1')
            ->setParameter('t0', $today)
            ->setParameter('t1', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();

        $avenir = (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.idRdv)')
            ->where('r.dateRdv >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'by_statut' => $byStatut,
            'a_venir' => $avenir,
            'aujourdhui' => $aujourdhui,
        ];
    }
}
