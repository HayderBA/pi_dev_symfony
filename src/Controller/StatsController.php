<?php

namespace App\Controller;

use App\Entity\SanteBienEtre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatsController extends AbstractController
{
    #[Route('/sante-bien-etre/stats', name: 'app_stats', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $records = $entityManager
            ->getRepository(SanteBienEtre::class)
            ->createQueryBuilder('s')
            ->orderBy('s.dateSuivi', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();

        $dates = [];
        $stressLevels = [];
        $sleepQualities = [];
        $statsRows = [];

        foreach ($records as $record) {
            $formattedDate = $record->getDateSuivi()?->format('Y-m-d');

            $dates[] = $formattedDate;
            $stressLevels[] = $record->getNiveauStress();
            $sleepQualities[] = $record->getQualiteSommeil();
            $statsRows[] = [
                'date' => $formattedDate,
                'stress_level' => $record->getNiveauStress(),
                'sleep_quality' => $record->getQualiteSommeil(),
            ];
        }

        return $this->render('stats/index.html.twig', [
            'dates' => $dates,
            'stress_levels' => $stressLevels,
            'sleep_qualities' => $sleepQualities,
            'stats_rows' => $statsRows,
        ]);
    }
}
