<?php

namespace App\Controller;

use App\Service\PsychologueMetierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontPsychologueController extends AbstractController
{
    #[Route('/medecins', name: 'front_psychologue_index')]
    #[Route('/psychologues', name: 'front_psychologue_index_alt')]
    #[Route('/psychologue', name: 'front_psychologue_index_legacy')]
    public function index(Request $request, PsychologueMetierService $psychologueMetier): Response
    {
        return $this->render('front/psychologue/index.html.twig', $psychologueMetier->buildListeData($request));
    }
}
