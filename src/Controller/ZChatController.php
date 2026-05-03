<?php

namespace App\Controller;

use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ZChatController extends AbstractController
{
    #[Route('/chat-assistant', name: 'app_simple_chat_final', methods: ['POST'])]
    public function chat(Request $request, GeminiService $gemini): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $query = $data['query'] ?? '';

        if (empty($query)) {
            return new JsonResponse(['success' => false, 'error' => 'Question vide'], 400);
        }

        try {
            $response = $gemini->simpleChat($query);
            return new JsonResponse(['success' => true, 'response' => $response]);
        } catch (\Throwable $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
