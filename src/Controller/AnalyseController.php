<?php

namespace App\Controller;

use App\Entity\QuizReponse;
use App\Repository\ForumPostRepository;
use App\Repository\QuizReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AnalyseController extends AbstractController
{
    #[Route('/api/analyse/sentiment', name: 'api_analyse_sentiment')]
    public function sentiment(ForumPostRepository $postRepo): JsonResponse
    {
        $posts = $postRepo->findBy([], ['dateCreation' => 'ASC']);
        
        $dates = [];
        $scores = [];
        $periodes = [
            'examens' => ['debut' => '15/05', 'fin' => '15/06', 'nom' => 'Période d\'examens'],
            'vacances' => ['debut' => '15/07', 'fin' => '15/08', 'nom' => 'Vacances d\'été'],
            'rentree' => ['debut' => '01/09', 'fin' => '30/09', 'nom' => 'Rentrée scolaire']
        ];
        
        foreach ($posts as $post) {
            $dates[] = $post->getDateCreation()->format('d/m');
            
            $contenu = strtolower($post->getContenu());
            $score = 3;
            
            if (strpos($contenu, 'stress') !== false) $score = 7;
            if (strpos($contenu, 'anxiété') !== false) $score = 8;
            if (strpos($contenu, 'crise') !== false) $score = 9;
            if (strpos($contenu, 'bien') !== false) $score = 2;
            if (strpos($contenu, 'fatigue') !== false) $score = 6;
            
            $scores[] = $score;
        }
        
        return $this->json([
            'dates' => $dates, 
            'scores' => $scores,
            'periodes' => $periodes
        ]);
    }
    
    #[Route('/api/quiz/repondre', name: 'api_quiz_repondre', methods: ['POST'])]
    public function quiz(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $score = $data['score'];
        
        $user = $this->getUser();
        if (!$user) {
            $user = $em->getRepository(\App\Entity\User::class)->find(37);
        }
        
        $quizReponse = new QuizReponse();
        $quizReponse->setUser($user);
        $quizReponse->setScore($score);
        $quizReponse->setCreatedAt(new \DateTimeImmutable());
        
        $em->persist($quizReponse);
        $em->flush();
        
        return $this->json(['success' => true]);
    }
}