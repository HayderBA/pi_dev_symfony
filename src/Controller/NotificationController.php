<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/api/notification/enregistrer-token', name: 'api_notification_token', methods: ['POST'])]
    public function enregistrerToken(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;

        if (!$token) {
            return $this->json(['error' => 'Token manquant'], 400);
        }

        $user = $this->getUser();
        if (!$user) {
            $user = $em->getRepository(User::class)->find(37);
        }

        $user->setFcmToken($token);
        $em->flush();

        return $this->json(['success' => true]);
    }
}