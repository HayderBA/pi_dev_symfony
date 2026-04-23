<?php

namespace App\Controller;

use App\Entity\QuizReponse;
use App\Repository\ForumPostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnalyseController extends AbstractController
{
    #[Route('/analyse', name: 'app_analyse_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('analyse/index.html.twig');
    }

    #[Route('/api/analyse/sentiment', name: 'api_analyse_sentiment')]
    public function sentiment(ForumPostRepository $postRepo): JsonResponse
    {
        $posts = $postRepo->findBy([], ['dateCreation' => 'ASC']);

        $dates = [];
        $scores = [];
        $periodes = [
            'examens' => ['debut' => '15/05', 'fin' => '15/06', 'nom' => "Periode d examens"],
            'vacances' => ['debut' => '15/07', 'fin' => '15/08', 'nom' => 'Vacances d ete'],
            'rentree' => ['debut' => '01/09', 'fin' => '30/09', 'nom' => 'Rentree scolaire'],
        ];

        foreach ($posts as $post) {
            $dates[] = $post->getDateCreation()?->format('d/m');
            $contenu = mb_strtolower((string) $post->getContenu());
            $score = 3;

            if (str_contains($contenu, 'stress')) {
                $score = 7;
            }
            if (str_contains($contenu, 'anxiete') || str_contains($contenu, 'anxiété')) {
                $score = 8;
            }
            if (str_contains($contenu, 'crise')) {
                $score = 9;
            }
            if (str_contains($contenu, 'bien')) {
                $score = 2;
            }
            if (str_contains($contenu, 'fatigue')) {
                $score = 6;
            }

            $scores[] = $score;
        }

        return $this->json([
            'dates' => $dates,
            'scores' => $scores,
            'periodes' => $periodes,
        ]);
    }

    #[Route('/api/quiz/repondre', name: 'api_quiz_repondre', methods: ['POST'])]
    public function quiz(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $score = (int) ($data['score'] ?? 0);

        $user = $userRepository->findAnyPatient() ?? $userRepository->findAnyMedecin();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Aucun utilisateur disponible.'], 400);
        }

        $quizReponse = new QuizReponse();
        $quizReponse->setUser($user);
        $quizReponse->setScore($score);

        $entityManager->persist($quizReponse);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}
