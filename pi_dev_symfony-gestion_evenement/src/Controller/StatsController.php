<?php
// src/Controller/StatsController.php

namespace App\Controller;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
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