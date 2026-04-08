<?php

namespace App\Controller;

use App\Repository\RessourceRepository;
use App\Repository\EvaluationRepository;
use App\Repository\FavoriRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function patient(): Response
    {
        return $this->render('back/patient.html.twig');
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
    // 👥 USERS MANAGEMENT
    // =========================
    /*#[Route('/users', name: 'app_admin_users')]
    public function users(): Response
    {
        return $this->render('back/users.html.twig');
    }

    // =========================
    // 📅 APPOINTMENTS
    // =========================
    #[Route('/appointments', name: 'app_admin_appointments')]
    public function appointments(): Response
    {
        return $this->render('back/appointments.html.twig');
    }*/
}