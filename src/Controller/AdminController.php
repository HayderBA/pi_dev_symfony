<?php

namespace App\Controller;

use App\Repository\RessourceRepository;
use App\Repository\EvaluationRepository;
use App\Repository\FavoriRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    // =========================
    // 🏠 DASHBOARD ADMIN
    // =========================
    #[Route('/admin_dash', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('back/admin.html.twig');
    }
    
    // =========================
    // 👨‍⚕️ DASHBOARD DOCTOR
    // =========================
    #[Route('/doctor', name: 'app_doctor_dashboard')]
    public function doctor(): Response
    {
        return $this->render('back/doctor.html.twig');
    }

    // =========================
    // 🧑‍💻 DASHBOARD PATIENT
    // =========================
    #[Route('/patient', name: 'app_patient_dashboard')]
    public function patientDashboard(): Response
    {
        return $this->render('back/patient/base_patient.html.twig');
    }

    #[Route('/patient/ressources', name: 'app_patient_ressources')]
    public function patientRessources(RessourceRepository $ressourceRepository, FavoriRepository $favoriRepository): Response
    {
        $userId = 1; // Demo
        $myFavoris = $favoriRepository->findBy(['userId' => $userId]);
        $favoriIds = array_map(fn($f) => $f->getRessource()->getId(), $myFavoris);

        return $this->render('back/patient/ressources.html.twig', [
            'ressources' => $ressourceRepository->findBy(['status' => 'PUBLISHED']),
            'favoriIds' => $favoriIds
        ]);
    }

    #[Route('/patient/mes-avis', name: 'app_patient_evaluations')]
    public function patientEvaluations(EvaluationRepository $evaluationRepository): Response
    {
        // For the demo, we assume userId = 1
        $userId = 1; 

        return $this->render('back/patient/evaluations.html.twig', [
            'my_evaluations' => $evaluationRepository->findBy(['userId' => $userId]),
        ]);
    }

    // =========================
    // 📅 CALENDAR EVENTS API
    // =========================
    #[Route('/calendar-events', name: 'app_admin_calendar_events')]
    public function calendarEvents(RessourceRepository $ressourceRepository, EvaluationRepository $evaluationRepository): JsonResponse
    {
        $events = [];

        foreach ($ressourceRepository->findAll() as $r) {
            if ($r->getDateCreation()) {
                $events[] = [
                    'title' => '📄 ' . $r->getTitle(),
                    'start' => $r->getDateCreation()->format('Y-m-d'),
                    'color' => '#1e88e5',
                    'url'   => '/ressource/' . $r->getId(),
                ];
            }
        }

        foreach ($evaluationRepository->findAll() as $e) {
            if ($e->getDateEvaluation()) {
                $events[] = [
                    'title' => '⭐ ' . ($e->getRessource() ? $e->getRessource()->getTitle() : 'Éval.') . ' (' . $e->getNote() . '/5)',
                    'start' => $e->getDateEvaluation()->format('Y-m-d'),
                    'color' => '#f59e0b',
                    'url'   => '/evaluation/' . $e->getId(),
                ];
            }
        }

        return new JsonResponse($events);
    }
}
