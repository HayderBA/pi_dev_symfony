<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/adminFakher')]
class AdminFakherController extends AbstractController
{
    // =========================
    // 🏠 DASHBOARD ADMIN
    // =========================
    #[Route('/admin_dash', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('back/admin_fakher.html.twig');
    }
    
    // =========================
    // 👨‍⚕️ DASHBOARD DOCTOR
    // =========================
    #[Route('/doctor', name: 'app_doctor_dashboard')]
    public function doctor(): Response
    {
        return $this->render('back/doctor_fakher.html.twig');
    }

    // =========================
    // 🧑‍💻 DASHBOARD PATIENT
    // =========================
    #[Route('/patient', name: 'app_patient_dashboard')]
    public function patient(): Response
    {
        return $this->render('back/patient.html.twig');
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