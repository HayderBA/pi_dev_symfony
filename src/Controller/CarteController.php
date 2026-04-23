<?php

namespace App\Controller;

use App\Repository\ForumPostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarteController extends AbstractController
{
    #[Route('/carte/medecins', name: 'app_carte_medecins')]
    public function medecins(UserRepository $userRepo, ForumPostRepository $forumPostRepository): Response
    {
        $medecins = array_map(function ($medecin) {
            return [
                'id' => $medecin->getId(),
                'nom' => $medecin->getFullName(),
                'latitude' => $medecin->getLatitude() ?? 36.8065,
                'longitude' => $medecin->getLongitude() ?? 10.1815,
                'adresse' => $medecin->getAdresse() ?? 'Tunis, Tunisie',
            ];
        }, $userRepo->findByRole('medecin'));

        $zonesAlerte = [];
        foreach ($forumPostRepository->findBy([], ['dateCreation' => 'DESC']) as $index => $post) {
            $contenu = mb_strtolower((string) $post->getContenu());
            if (str_contains($contenu, 'urgence') || str_contains($contenu, 'suicide') || str_contains($contenu, 'detresse') || str_contains($contenu, 'détresse')) {
                $zonesAlerte[] = [
                    'nom' => 'Zone ' . ($post->getCategorie() ?? 'Forum'),
                    'latitude' => 36.8065 + (($index + 1) / 1000),
                    'longitude' => 10.1815 + (($index + 1) / 1000),
                    'rayon' => 2500,
                    'niveau' => 'eleve',
                ];
            }
        }

        if ($zonesAlerte === []) {
            $zonesAlerte[] = [
                'nom' => 'Centre Tunis',
                'latitude' => 36.8065,
                'longitude' => 10.1815,
                'rayon' => 3000,
                'niveau' => 'moyen',
            ];
        }

        return $this->render('carte/medecins.html.twig', [
            'medecins' => $medecins,
            'zonesAlerte' => $zonesAlerte,
        ]);
    }
}
