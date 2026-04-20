<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarteController extends AbstractController
{
    #[Route('/carte/medecins', name: 'carte_medecins')]
    public function medecins(UserRepository $userRepo): Response
    {
        $medecins = $userRepo->findBy(['role' => 'medecin']);
        
        $medecinsData = [];
        foreach ($medecins as $medecin) {
            // Coordonnées des médecins (à remplacer par vos vraies données)
            $medecinsData[] = [
                'id' => $medecin->getId(),
                'nom' => $medecin->getName() . ' ' . $medecin->getSecondName(),
                'latitude' => $medecin->getLatitude() ?? 36.8065,
                'longitude' => $medecin->getLongitude() ?? 10.1815,
                'adresse' => $medecin->getAdresse() ?? 'Tunis, Tunisie'
            ];
        }
        
        // Zones d'alerte (basées sur les posts urgents)
        $zonesAlerte = [
            [
                'nom' => 'Centre Tunis',
                'latitude' => 36.8065,
                'longitude' => 10.1815,
                'rayon' => 3000,
                'niveau' => 'élevé'
            ]
        ];
        
        return $this->render('carte/medecins.html.twig', [
            'medecins' => $medecinsData,
            'zonesAlerte' => $zonesAlerte
        ]);
    }
}