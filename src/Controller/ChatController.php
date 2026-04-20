<?php

namespace App\Controller;

use App\Service\MedecinIAService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    private $pdo;
    
    public function __construct()
    {
        $this->pdo = new \PDO('mysql:host=127.0.0.1;dbname=pi_dev;charset=utf8', 'root', '');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    
    #[Route('/chat', name: 'chat_index')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('forum/sidebar.html.twig', [
            'conversation_id' => 1
        ]);
    }
    
    #[Route('/chat/send', name: 'chat_send', methods: ['POST'])]
    public function send(Request $request, MedecinIAService $ia): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';
        
        if (empty($message)) {
            return $this->json(['error' => 'Message vide'], 400);
        }
        
        $reponse = $ia->getReponse($message);
        $isUrgent = $ia->isUrgent($message);
        
        return $this->json([
            'success' => true,
            'reponse' => $reponse,
            'isUrgent' => $isUrgent
        ]);
    }
}