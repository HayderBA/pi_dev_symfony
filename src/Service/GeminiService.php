<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private const MODEL   = 'gemini-2.5-flash';
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey
    ) {}

    private const MAX_RETRIES     = 4;
    private const RETRYABLE_CODES = [429, 500, 502, 503, 504];

    private function generate(array $contents, int $attempt = 0): string
    {
        $url = sprintf(self::API_URL, self::MODEL, $this->apiKey);

        $response = $this->httpClient->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json'    => [
                'contents'         => $contents,
                'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 2048],
            ],
            'timeout' => 90,
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            $data = $response->toArray();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        }

        $body     = $response->getContent(false);
        $bodyData = json_decode($body, true) ?? [];

        // Retry on transient errors (429 rate-limit, 503 overload, other 5xx)
        if (in_array($statusCode, self::RETRYABLE_CODES, true) && $attempt < self::MAX_RETRIES) {
            sleep($this->resolveRetryDelay($bodyData, $attempt));
            return $this->generate($contents, $attempt + 1);
        }

        throw new \RuntimeException(
            sprintf('Gemini API error %d (after %d attempt(s)) — %s', $statusCode, $attempt + 1, $body)
        );
    }

    private function resolveRetryDelay(array $bodyData, int $attempt): int
    {
        // Honour the retryDelay hint from the API when present (e.g. "26s")
        foreach ($bodyData['error']['details'] ?? [] as $detail) {
            if (isset($detail['retryDelay'])) {
                $seconds = (int) filter_var($detail['retryDelay'], FILTER_SANITIZE_NUMBER_INT);
                return min($seconds + 2, 65);
            }
        }

        // Exponential backoff fallback: 3s → 6s → 12s → 24s
        return min(3 * (2 ** $attempt), 30);
    }

    private function extractJson(string $text): ?array
    {
        // Strip markdown code fences if present
        $text = preg_replace('/```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/```/', '', $text);

        preg_match('/\{.*\}/s', trim($text), $matches);

        if (!empty($matches[0])) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 1 — PDF Ressource Summarizer
    // ─────────────────────────────────────────────────────────────────────────
    public function analyzePdf(string $pdfBase64): array
    {
        $prompt = <<<EOT
Tu es un assistant pour une plateforme de santé mentale (GrowMind).
Analyse ce document PDF et retourne un JSON UNIQUEMENT (sans markdown, sans texte avant ou après) avec ces champs:
{
  "title": "Titre accrocheur max 60 caractères en français",
  "description": "Description de 2 phrases en français, claire et engageante",
  "category": "l'une de ces valeurs exactes: Santé | Bien-être | Développement personnel",
  "content": "Résumé de 3-4 phrases en français du contenu principal du document"
}
EOT;

        $contents = [[
            'parts' => [
                ['inline_data' => ['mime_type' => 'application/pdf', 'data' => $pdfBase64]],
                ['text' => $prompt],
            ],
        ]];

        $text   = $this->generate($contents);
        $parsed = $this->extractJson($text);

        if ($parsed) {
            return ['success' => true] + $parsed;
        }

        return ['success' => false, 'error' => 'Impossible de parser la réponse IA.', 'raw' => $text];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 2 — Evaluation Sentiment Dashboard
    // ─────────────────────────────────────────────────────────────────────────
    public function evaluationInsights(array $evaluations): string
    {
        if (empty($evaluations)) {
            return 'Aucune évaluation disponible pour générer une analyse.';
        }

        $lines = '';
        foreach ($evaluations as $e) {
            $comment = $e->getCommentaire() ?: '(sans commentaire)';
            $resource = $e->getRessource()?->getTitle() ?? 'Ressource inconnue';
            $lines .= "• [{$resource}] Note: {$e->getNote()}/5 — \"{$comment}\"\n";
        }

        $total  = count($evaluations);
        $prompt = <<<EOT
Tu es analyste senior pour GrowMind, une plateforme de santé mentale.
Voici {$total} évaluations de patients:

{$lines}

Rédige en français un paragraphe analytique de 4-5 phrases (texte direct, sans liste, sans titre) qui:
1. Exprime le sentiment global des patients avec des données concrètes
2. Identifie 1 point fort et 1 point d'amélioration  
3. Formule une recommandation actionnable pour les administrateurs
Commence directement par l'analyse.
EOT;

        return $this->generate([['parts' => [['text' => $prompt]]]]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 3 — Patient Ressource Recommender
    // ─────────────────────────────────────────────────────────────────────────
    public function recommendRessources(array $favoris, array $myEvaluations, array $allRessources): array
    {
        if (empty($allRessources)) {
            return [];
        }

        $favoriteTitles = array_filter(array_map(
            fn($f) => $f->getRessource()?->getTitle(),
            $favoris
        ));

        $evalLines = '';
        foreach ($myEvaluations as $e) {
            if ($e->getRessource()) {
                $evalLines .= "  - \"{$e->getRessource()->getTitle()}\": {$e->getNote()}/5\n";
            }
        }

        $catalogue = '';
        $idMap     = [];
        foreach ($allRessources as $r) {
            $catalogue            .= "ID:{$r->getId()} | Titre: \"{$r->getTitle()}\" | Catégorie: {$r->getCategory()} | Type: {$r->getType()}\n";
            $idMap[$r->getId()]    = $r;
        }

        $favorites = implode(', ', $favoriteTitles) ?: 'aucune encore';

        $prompt = <<<EOT
Tu es le moteur de recommandation de GrowMind (plateforme santé mentale).

Profil patient:
- Ressources mises en favoris: {$favorites}
- Évaluations données:
{$evalLines}

Catalogue disponible:
{$catalogue}

Recommande les 3 ressources les plus pertinentes pour ce patient.
Réponds UNIQUEMENT avec ce JSON valide (sans markdown):
{"recommendations": [{"id": 1, "reason": "Explication courte en français"}, {"id": 2, "reason": "..."}, {"id": 3, "reason": "..."}]}
EOT;

        $text   = $this->generate([['parts' => [['text' => $prompt]]]]);
        $parsed = $this->extractJson($text);

        if (!$parsed || !isset($parsed['recommendations'])) {
            return [];
        }

        $result = [];
        foreach ($parsed['recommendations'] as $rec) {
            $id = $rec['id'] ?? null;
            if ($id && isset($idMap[$id])) {
                $result[] = [
                    'ressource' => $idMap[$id],
                    'reason'    => $rec['reason'] ?? '',
                ];
            }
        }

        return $result;
    }
}
