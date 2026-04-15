<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Repository\RessourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontRessourceController extends AbstractController
{
    #[Route('/resources', name: 'front_resources', methods: ['GET'])]
    public function index(RessourceRepository $ressourceRepository): Response
    {
        $ressources = $ressourceRepository->findBy(
            ['status' => 'PUBLISHED'],
            ['dateCreation' => 'DESC']
        );

        return $this->render('front/ressource/index.html.twig', [
            'ressources' => $ressources,
        ]);
    }

    #[Route('/resources/{id}', name: 'front_resource_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Ressource $ressource): Response
    {
        if ($ressource->getStatus() !== 'PUBLISHED') {
            throw $this->createNotFoundException('Resource not found.');
        }

        return $this->render('front/ressource/show.html.twig', [
            'ressource' => $ressource,
        ]);
    }
}