<?php

namespace App\Controller;


use App\Entity\Admin;
use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;

#[Route('/doctor')]
class DoctorController extends AbstractController
{
    // =========================
    // 👨‍⚕️ DASHBOARD DOCTOR
    // =========================
    #[Route('/doctor_dash', name: 'app_doctor_dash')]
    public function doctor(ManagerRegistry $doctrine): Response
    {
        // ✅ Vérifier connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if (!$this->isGranted('ROLE_DOCTOR')) {
            return $this->redirectToRoute('app_home');
        }

        $em = $doctrine->getManager();

        // ✅ Récupérer l'entité Doctor liée à l'user connecté
        $doctor = $em->getRepository(Doctor::class)->findOneBy([
            'user' => $this->getUser()
        ]);

        return $this->render('back/doctor.html.twig', [
            'doctor' => $doctor,  // ✅ entité Doctor avec specialty, experience, etc.
        ]);
    }

}