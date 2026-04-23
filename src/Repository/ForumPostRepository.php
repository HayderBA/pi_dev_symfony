<?php

namespace App\Repository;

use App\Entity\ForumPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForumPost>
 */
class ForumPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumPost::class);
    }

    /**
     * @return ForumPost[]
     */
    public function findPublicPosts(?string $search = null, ?string $categorie = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.archive = :archive')
            ->setParameter('archive', false)
            ->orderBy('p.dateCreation', 'DESC');

        if ($search !== null && $search !== '') {
            $qb
                ->andWhere('p.nom LIKE :search OR p.contenu LIKE :search OR p.categorie LIKE :search OR p.role LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categorie !== null && $categorie !== '') {
            $qb
                ->andWhere('p.categorie = :categorie')
                ->setParameter('categorie', $categorie);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return string[]
     */
    public function findCategories(): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('DISTINCT p.categorie AS categorie')
            ->andWhere('p.archive = :archive')
            ->setParameter('archive', false)
            ->orderBy('p.categorie', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_values(array_filter(array_map(static fn (array $row) => $row['categorie'] ?? null, $rows)));
    }
}
