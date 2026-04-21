<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedbackController extends AbstractController
{
    #[Route('/feedback/{userId}', name: 'feedback_form')]
    public function index($userId, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $note = $request->get('note');
            $message = $request->get('message');
            
            // Sauvegarder le feedback (à implémenter plus tard)
            
            $this->addFlash('success', 'Merci pour votre feedback !');
            return $this->redirectToRoute('forum_index');
        }
        
        return $this->render('feedback/index.html.twig', [
            'userId' => $userId
        ]);
    }
}