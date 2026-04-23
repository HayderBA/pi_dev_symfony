<?php

namespace App\Controller;

use App\Entity\Cabinet;
use App\Repository\PsychologueRepository;
use App\Service\CabinetMetierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontCabinetController extends AbstractController
{
    #[Route('/cabinets', name: 'front_cabinet_index')]
    #[Route('/cabinet', name: 'front_cabinet_index_legacy')]
    public function index(Request $request, CabinetMetierService $cabinetMetier): Response
    {
        return $this->render('front/cabinet_index.html.twig', $cabinetMetier->buildListeData($request));
    }

    #[Route('/cabinets/{id}', name: 'front_cabinet_show')]
    #[Route('/cabinet/{id}', name: 'front_cabinet_show_legacy')]
    public function show(Cabinet $cabinet, PsychologueRepository $psychologueRepository): Response
    {
        return $this->render('front/cabinet_show.html.twig', [
            'cabinet' => $cabinet,
            'psychologues' => $psychologueRepository->findByCabinetId($cabinet->getIdCabinet()),
        ]);
    }
}
