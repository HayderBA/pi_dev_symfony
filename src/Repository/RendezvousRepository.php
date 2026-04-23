<?php

namespace App\Repository;

use App\Entity\Rendezvous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rendezvous>
 */
class RendezvousRepository extends ServiceEntityRepository
{
    private const SORT_FIELDS = [
        'date' => 'r.dateRdv',
        'id' => 'r.idRdv',
        'statut' => 'r.statut',
        'heure' => 'r.heure',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rendezvous::class);
    }

    /**
     * @return Rendezvous[]
     */
    public function findFiltered(?string $search, ?string $statut, ?\DateTimeInterface $dateFrom, ?\DateTimeInterface $dateTo, string $sort = 'date', string $direction = 'desc'): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.psychologue', 'p')->addSelect('p')
            ->leftJoin('p.cabinet', 'c')->addSelect('c')
            ->leftJoin('p.user', 'u')->addSelect('u');

        if ($search) {
            $q = '%' . mb_strtolower($search) . '%';
            $qb->andWhere('LOWER(r.nom_patient) LIKE :q OR LOWER(r.prenom_patient) LIKE :q OR LOWER(r.email_patient) LIKE :q OR LOWER(p.nom) LIKE :q OR LOWER(p.prenom) LIKE :q OR LOWER(c.nomcabinet) LIKE :q')
                ->setParameter('q', $q);
        }

        if ($statut) {
            $qb->andWhere('r.statut = :statut')->setParameter('statut', $statut);
        }

        if ($dateFrom) {
            $qb->andWhere('r.dateRdv >= :df')->setParameter('df', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('r.dateRdv <= :dt')->setParameter('dt', $dateTo);
        }

        $sortField = self::SORT_FIELDS[$sort] ?? self::SORT_FIELDS['date'];
        $dir = 'ASC' === strtoupper($direction) ? 'ASC' : 'DESC';

        return $qb->orderBy($sortField, $dir)->addOrderBy('r.heure', $dir)->addOrderBy('r.idRdv', 'DESC')->getQuery()->getResult();
    }

    /**
     * @return array{total:int,by_statut:array<string,int>,a_venir:int,aujourdhui:int}
     */
    public function getStatistics(): array
    {
        $total = (int) $this->createQueryBuilder('r')->select('COUNT(r.idRdv)')->getQuery()->getSingleScalarResult();
        $raw = $this->createQueryBuilder('r')->select('r.statut AS st, COUNT(r.idRdv) AS cnt')->groupBy('r.statut')->getQuery()->getArrayResult();

        $byStatut = [];
        foreach ($raw as $row) {
            $byStatut[(string) $row['st']] = (int) $row['cnt'];
        }

        $today = new \DateTimeImmutable('today');
        $tomorrow = $today->modify('+1 day');

        $aujourdhui = (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.idRdv)')
            ->where('r.dateRdv >= :today AND r.dateRdv < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();

        $avenir = (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.idRdv)')
            ->where('r.dateRdv >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        return ['total' => $total, 'by_statut' => $byStatut, 'a_venir' => $avenir, 'aujourdhui' => $aujourdhui];
    }

    public function countPendingRemindersForTomorrow(): int
    {
        $tomorrow = (new \DateTimeImmutable('+1 day'))->setTime(0, 0, 0);
        $dayAfter = $tomorrow->modify('+1 day');

        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.idRdv)')
            ->where('r.dateRdv >= :tomorrow')
            ->andWhere('r.dateRdv < :dayAfter')
            ->andWhere('r.statut != :annule')
            ->andWhere('r.rappel_envoye = false OR r.rappel_envoye IS NULL')
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('dayAfter', $dayAfter)
            ->setParameter('annule', 'annule')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Rendezvous[]
     */
    public function findUpcoming(int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.psychologue', 'p')->addSelect('p')
            ->leftJoin('p.cabinet', 'c')->addSelect('c')
            ->where('r.dateRdv >= :today')
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('r.dateRdv', 'ASC')
            ->addOrderBy('r.heure', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
