<?php

namespace App\Controller;

use App\Entity\SanteBienEtre;
use App\Repository\EvenementRepository;
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

    #[Route('/admin/stats', name: 'app_admin_stats')]
    public function stats(EvenementRepository $eventRepo): Response
    {
        $evenements = $eventRepo->findAll();
        
        $totalEvents = count($evenements);
        $totalReservations = 0;
        $totalCapacity = 0;
        $eventsByMonth = [];
        
        foreach ($evenements as $event) {
            $totalReservations += $event->getCurrentReservationsCount();
            $totalCapacity += $event->getMaxCapacity();
            $month = $event->getDate()->format('F Y');
            if (!isset($eventsByMonth[$month])) {
                $eventsByMonth[$month] = ['events' => 0, 'reservations' => 0];
            }
            $eventsByMonth[$month]['events']++;
            $eventsByMonth[$month]['reservations'] += $event->getCurrentReservationsCount();
        }
        
        $occupancyRate = $totalCapacity > 0 ? ($totalReservations / $totalCapacity) * 100 : 0;
        
        return $this->render('admin/stats.html.twig', [
            'totalEvents' => $totalEvents,
            'totalReservations' => $totalReservations,
            'occupancyRate' => round($occupancyRate, 1),
            'eventsByMonth' => $eventsByMonth,
            'evenements' => $evenements
        ]);
    }
}