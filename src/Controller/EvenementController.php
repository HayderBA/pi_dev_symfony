<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Service\EventImpactService;
use App\Service\EventMarketingLabService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EvenementController extends AbstractController
{
    private function getVenueCatalog(): array
    {
        return [
            [
                'label' => 'Yoga Home',
                'category' => 'Yoga',
                'city' => 'Tunis',
                'address' => '9 Rue Salem Bouhajeb, Mutuelleville, 1082 Tunis, Tunisie',
                'lat' => 36.8188,
                'lng' => 10.1719,
            ],
            [
                'label' => 'FitYogaProf',
                'category' => 'Yoga',
                'city' => 'Tunis',
                'address' => '5 Rue de Jeune Foyer, El Menzah 1, 1004 Tunis, Tunisie',
                'lat' => 36.8424,
                'lng' => 10.1678,
            ],
            [
                'label' => 'M Pilates',
                'category' => 'Pilates',
                'city' => 'Tunis',
                'address' => '9 Route de Carthage, Sidi Daoud, Tunis, Tunisie',
                'lat' => 36.8649,
                'lng' => 10.2853,
            ],
            [
                'label' => 'Ecole Oxygene Pilates',
                'category' => 'Pilates',
                'city' => 'La Marsa',
                'address' => 'Rue Mohamed Bairem 5, Sidi Daoued, La Marsa, Tunisie',
                'lat' => 36.8731,
                'lng' => 10.2965,
            ],
            [
                'label' => 'Parc du Belvedere',
                'category' => 'Parc',
                'city' => 'Tunis',
                'address' => 'Avenue Taieb Mhiri, Tunis, Tunisie',
                'lat' => 36.8181,
                'lng' => 10.1815,
            ],
            [
                'label' => 'Essentia Studio',
                'category' => 'Pilates & Yoga',
                'city' => 'Ariana',
                'address' => 'Residence Fares, Ariana, Tunisie',
                'lat' => 36.8662,
                'lng' => 10.1954,
            ],
            [
                'label' => 'Pyramid Fitness',
                'category' => 'Pilates & Fitness',
                'city' => 'Ariana',
                'address' => '118 Avenue Ibn Khaldoun, Riadh El Andalous, 2058 Ariana, Tunisie',
                'lat' => 36.9078,
                'lng' => 10.1257,
            ],
            [
                'label' => 'Hippoclub',
                'category' => 'Centre equestre',
                'city' => 'Ariana',
                'address' => 'Sidi Thabet, 2020 Ariana, Tunisie',
                'lat' => 36.9119,
                'lng' => 10.0394,
            ],
            [
                'label' => 'The YOGA Studio',
                'category' => 'Yoga',
                'city' => 'Sousse',
                'address' => '10 Rue Rayahin, Khezema Est, 4051 Sousse, Tunisie',
                'lat' => 35.8392,
                'lng' => 10.6255,
            ],
            [
                'label' => 'Hannibal Park',
                'category' => 'Parc',
                'city' => 'Hammam-Sousse',
                'address' => 'Zone touristique Kantaoui, Hammam-Sousse, Tunisie',
                'lat' => 35.8920,
                'lng' => 10.5958,
            ],
        ];
    }

    #[Route('/evenement', name: 'app_evenement_list', methods: ['GET'])]
    #[Route('/events', name: 'app_events_list', methods: ['GET'])]
    public function list(EvenementRepository $repository, Request $request, EventMarketingLabService $marketingLab, EventImpactService $impactService): Response
    {
        $search = trim((string) $request->query->get('search', ''));
        $filterDate = trim((string) $request->query->get('date', ''));

        if ($filterDate !== '') {
            $startDate = \DateTime::createFromFormat('Y-m-d', $filterDate) ?: new \DateTime($filterDate);
            $endDate = clone $startDate;
            $evenements = $repository->findEventsBetweenDates($startDate, $endDate);
        } elseif ($search !== '') {
            $evenements = $repository->searchEvents($search);
        } else {
            $evenements = $repository->findBy([], ['date' => 'ASC']);
        }

        foreach ($evenements as $evenement) {
            $evenement->updateDynamicPrice();
        }

        $calendarEvents = array_map(
            static fn (Evenement $event) => $event->getCalendarEvent(),
            $repository->findBy([], ['date' => 'ASC'])
        );

        $marketingExperience = $marketingLab->buildMarketingExperience($evenements);
        $impactExperience = $impactService->buildForEvents($evenements);

        return $this->render('front/evenement_index.html.twig', [
            'evenements' => $evenements,
            'search' => $search,
            'selectedDate' => $filterDate,
            'calendarEventsJson' => json_encode($calendarEvents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'marketingCampaigns' => $marketingExperience['campaigns'],
            'eventMarketingMeta' => $marketingExperience['eventMeta'],
            'videoAds' => $marketingExperience['videoAds'],
            'solidarityPartners' => $impactExperience['partners'],
            'eventImpactMeta' => $impactExperience['meta'],
        ]);
    }

    #[Route('/evenement/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement, EvenementRepository $repository, EventImpactService $impactService): Response
    {
        $evenement->updateDynamicPrice();
        $recommendations = $evenement->getRecommendations($repository->findUpcomingEvents(), 2);
        $solidarity = $impactService->getForEvent($evenement);

        return $this->render('front/evenement_show.html.twig', [
            'evenement' => $evenement,
            'recommendations' => $recommendations,
            'occupancyRate' => $evenement->getOccupancyRate(),
            'popularityBadge' => $evenement->getPopularityBadge(),
            'rowPrices' => $evenement->getRowPrices(),
            'seatCategories' => $evenement->getSeatCategories(),
            'venueCatalogJson' => json_encode($this->getVenueCatalog(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'solidarity' => $solidarity,
        ]);
    }

    #[Route('/admin/evenement', name: 'app_evenement_admin', methods: ['GET'])]
    public function admin(EvenementRepository $repository): Response
    {
        return $this->render('back/evenement_admin.html.twig', [
            'evenements' => $repository->findBy([], ['date' => 'DESC']),
        ]);
    }

    #[Route('/admin/evenement/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $evenement->setDate(new \DateTime('+1 day'));
        $evenement->setBasePrice(50);
        $evenement->setDynamicPrice(50);
        $evenement->setMaxCapacity(80);

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $evenement->updateDynamicPrice();
            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès.');

            return $this->redirectToRoute('app_evenement_admin');
        }

        return $this->render('back/evenement_new.html.twig', [
            'form' => $form->createView(),
            'venueCatalogJson' => json_encode($this->getVenueCatalog(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    #[Route('/admin/evenement/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $evenement->updateDynamicPrice();
            $em->flush();

            $this->addFlash('success', 'Événement modifié avec succès.');

            return $this->redirectToRoute('app_evenement_admin');
        }

        return $this->render('back/evenement_edit.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
            'venueCatalogJson' => json_encode($this->getVenueCatalog(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    #[Route('/admin/evenement/{id}/delete', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), (string) $request->request->get('_token'))) {
            $em->remove($evenement);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé avec succès.');
        }

        return $this->redirectToRoute('app_evenement_admin');
    }

    #[Route('/admin/evenement/{id}', name: 'app_evenement_show_admin', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showAdmin(int $id, EvenementRepository $repository): Response
    {
        $evenement = $repository->find($id);
        if (!$evenement) {
            $this->addFlash('error', 'Événement non trouvé.');

            return $this->redirectToRoute('app_evenement_admin');
        }

        return $this->render('back/evenement_show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/admin/evenement/stats', name: 'app_evenement_stats', methods: ['GET'])]
    public function stats(EvenementRepository $repository): Response
    {
        $evenements = $repository->findAll();
        $totalEvents = count($evenements);
        $totalReservations = 0;
        $totalCapacity = 0;

        foreach ($evenements as $event) {
            $totalReservations += $event->getCurrentReservationsCount();
            $totalCapacity += $event->getMaxCapacity();
        }

        return $this->render('back/stats_evenement.html.twig', [
            'totalEvents' => $totalEvents,
            'totalReservations' => $totalReservations,
            'totalCapacity' => $totalCapacity,
            'occupancyRate' => $totalCapacity > 0 ? round(($totalReservations / $totalCapacity) * 100, 1) : 0,
            'evenements' => $evenements,
        ]);
    }

    #[Route('/evenement/calendar/events', name: 'app_calendar_events', methods: ['GET'])]
    public function calendarEvents(EvenementRepository $repository): JsonResponse
    {
        $events = array_map(
            static fn (Evenement $evenement) => $evenement->getCalendarEvent(),
            $repository->findBy([], ['date' => 'ASC'])
        );

        return $this->json($events);
    }

    #[Route('/evenement/{id}/ical', name: 'app_evenement_ical', methods: ['GET'])]
    public function exportIcal(Evenement $evenement): Response
    {
        return new Response($evenement->getIcalContent(), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="growmind-event-' . $evenement->getId() . '.ics"',
        ]);
    }
}
