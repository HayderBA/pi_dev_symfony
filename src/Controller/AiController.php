<?php

namespace App\Controller;

use App\Repository\EvaluationRepository;
use App\Repository\FavoriRepository;
use App\Repository\RessourceRepository;
use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ai')]
class AiController extends AbstractController
{
    public function __construct(private GeminiService $gemini) {}

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 1 — PDF Summarizer (accepts multipart PDF, returns field suggestions)
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/analyze-pdf', name: 'app_ai_analyze_pdf', methods: ['POST'])]
    public function analyzePdf(Request $request): JsonResponse
    {
        $file = $request->files->get('pdf');

        if (!$file) {
            return $this->json(['success' => false, 'error' => 'Aucun fichier reçu.'], 400);
        }

        $mime = $file->getMimeType();
        $okMime = \in_array($mime, [
            'application/pdf',
            'application/x-pdf',
            'application/octet-stream',
        ], true);
        $okExt = str_ends_with(strtolower($file->getClientOriginalName()), '.pdf');
        if (!$okExt && !$okMime) {
            return $this->json(['success' => false, 'error' => 'Le fichier doit être un PDF (.pdf).'], 400);
        }

        $maxSize = 15 * 1024 * 1024; // 15 MB
        if ($file->getSize() > $maxSize) {
            return $this->json(['success' => false, 'error' => 'PDF trop volumineux (max 15 Mo).'], 400);
        }

        try {
            $pdfBase64 = base64_encode(file_get_contents($file->getPathname()));
            $result    = $this->gemini->analyzePdf($pdfBase64);
            return $this->json($result);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 2 — Evaluation Sentiment Insights
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/evaluation-insights', name: 'app_ai_evaluation_insights', methods: ['GET'])]
    public function evaluationInsights(EvaluationRepository $evaluationRepository): JsonResponse
    {
        try {
            $evaluations = $evaluationRepository->findAll();
            $insight     = $this->gemini->evaluationInsights($evaluations);
            return $this->json(['success' => true, 'insight' => $insight]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 3 — Patient Ressource Recommender
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/recommendations', name: 'app_ai_recommendations', methods: ['GET'])]
    public function recommendations(
        FavoriRepository    $favoriRepository,
        EvaluationRepository $evaluationRepository,
        RessourceRepository  $ressourceRepository
    ): JsonResponse {
        $userId = 1; // Demo — replace with actual user session when auth is ready

        try {
            $favoris       = $favoriRepository->findBy(['userId' => $userId]);
            $myEvals       = $evaluationRepository->findBy(['userId' => $userId]);
            $allRessources = $ressourceRepository->findBy(['status' => 'PUBLISHED']);

            $recommendations = $this->gemini->recommendRessources($favoris, $myEvals, $allRessources);

            $payload = array_map(fn($rec) => [
                'id'          => $rec['ressource']->getId(),
                'title'       => $rec['ressource']->getTitle(),
                'description' => $rec['ressource']->getDescription(),
                'type'        => $rec['ressource']->getType(),
                'category'    => $rec['ressource']->getCategory(),
                'reason'      => $rec['reason'],
            ], array_values($recommendations));

            return $this->json(['success' => true, 'recommendations' => $payload]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 4 — GrowMind AI Chatbot (Q&A about Resources)
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/assistant/message', name: 'app_ai_assistant_send', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $query = $data['query'] ?? '';

        if (empty($query)) {
            return $this->json(['success' => false, 'error' => 'Question vide'], 400);
        }

        try {
            $response = $this->gemini->simpleChat($query);
            return $this->json(['success' => true, 'response' => $response]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
