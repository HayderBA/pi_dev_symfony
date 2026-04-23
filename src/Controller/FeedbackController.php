<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedbackController extends AbstractController
{
    #[Route('/feedback', name: 'feedback_default')]
    #[Route('/feedback/{userId}', name: 'feedback_form')]
    public function index(?int $userId, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userId ? $userRepository->find($userId) : ($userRepository->findAnyPatient() ?? $userRepository->findAnyMedecin());

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        if ($request->isMethod('POST')) {
            $note = (int) $request->request->get('note', 0);
            if ($note < 1 || $note > 5) {
                $this->addFlash('error', 'Veuillez choisir une note entre 1 et 5.');

                return $this->redirectToRoute('feedback_form', ['userId' => $user->getId()]);
            }

            $feedback = new Feedback();
            $feedback->setUser($user);
            $feedback->setNote($note);
            $feedback->setMessage(trim((string) $request->request->get('message', '')) ?: null);

            $entityManager->persist($feedback);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre feedback.');
            return $this->redirectToRoute('feedback_form', ['userId' => $user->getId()]);
        }

        return $this->render('feedback/index.html.twig', [
            'userId' => $user->getId(),
            'user' => $user,
        ]);
    }
}
