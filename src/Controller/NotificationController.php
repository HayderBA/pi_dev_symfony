<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/api/notification/enregistrer-token', name: 'api_notification_token', methods: ['POST'])]
    public function enregistrerToken(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $token = $data['token'] ?? null;

        if (!$token) {
            return $this->json(['error' => 'Token manquant'], 400);
        }

        $user = $userRepository->findAnyPatient() ?? $userRepository->findAnyMedecin();
        if (!$user) {
            return $this->json(['error' => 'Aucun utilisateur disponible'], 400);
        }

        $user->setFcmToken($token);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}
