<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\UserRepository;
use App\Service\MedecinIAService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/chat', name: 'app_chat_index')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('chat/index.html.twig', [
            'medecins' => $userRepository->findByRole('medecin'),
        ]);
    }

    #[Route('/messages/new/{userId}', name: 'messages_new')]
    public function newConversation(int $userId, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $medecin = $userRepository->find($userId);
        $patient = $userRepository->findAnyPatient();

        if (!$medecin || !$patient) {
            $this->addFlash('warning', 'Conversation impossible pour le moment.');
            return $this->redirectToRoute('app_chat_index');
        }

        $conversation = new Conversation();
        $conversation->addParticipant($patient);
        $conversation->addParticipant($medecin);
        $conversation->setIsIA(false);

        $message = new Message();
        $message->setSender($patient);
        $message->setContent('Bonjour Docteur, je souhaite prendre contact via GrowMind.');
        $conversation->addMessage($message);

        $entityManager->persist($conversation);
        $entityManager->persist($message);
        $entityManager->flush();

        $this->addFlash('success', 'Conversation creee avec succes.');
        return $this->redirectToRoute('app_chat_index');
    }

    #[Route('/chat/send', name: 'chat_send', methods: ['POST'])]
    public function send(Request $request, MedecinIAService $ia): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $message = trim((string) ($data['message'] ?? ''));

        if ($message === '') {
            return $this->json(['error' => 'Message vide'], 400);
        }

        return $this->json([
            'success' => true,
            'reponse' => $ia->getReponse($message),
            'isUrgent' => $ia->isUrgent($message),
        ]);
    }
}
