<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('front/contact.html.twig');
    }
    #[Route('/doctors', name: 'app_doctors')]
    public function doctors(): Response
    {
        return $this->render('front/doctors.html.twig');
    }
    
    #[Route('/appointment', name: 'app_appointment')]
    public function appointment(): Response
    {
        return $this->render('front/appointment.html.twig');
    }
    
    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        // 🔐 Vérifie utilisateur connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front/profile.html.twig', [
            'currentUser' => $this->getUser(),
            'sessionRole' => $this->getUser()->getRoles(),
        ]);
    }
}