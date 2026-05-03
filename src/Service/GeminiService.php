<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s';
    private const LIST_MODELS_URL = 'https://generativelanguage.googleapis.com/v1beta/models?key=%s';

    private ?string $resolvedModel = null;

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $model = 'gemini-2.5-flash'
    ) {}

    private function normalizeModelName(string $model): string
    {
        $model = trim($model);
        if ($model === '') {
            return 'gemini-2.5-flash';
        }

        if (str_starts_with($model, 'models/')) {
            return substr($model, 7);
        }

        return $model;
    }

    private function currentModel(): string
    {
        return $this->resolvedModel ?? $this->normalizeModelName($this->model);
    }

    private function autoPickModel(?string $excludeModel = null): ?string
    {
        try {
            $url = sprintf(self::LIST_MODELS_URL, $this->apiKey);
            $response = $this->httpClient->request('GET', $url, ['timeout' => 15]);
            $data = $response->toArray(false);

            $models = $data['models'] ?? [];
            if (!\is_array($models)) {
                return null;
            }

            $candidates = [];
            foreach ($models as $m) {
                if (!\is_array($m)) {
                    continue;
                }
                $name = (string)($m['name'] ?? '');
                $methods = $m['supportedGenerationMethods'] ?? [];
                if (!\is_array($methods) || !\in_array('generateContent', $methods, true)) {
                    continue;
                }
                if ($name === '' || !str_starts_with($name, 'models/gemini-')) {
                    continue;
                }
                $candidates[] = $this->normalizeModelName($name);
            }

            if ($excludeModel !== null) {
                $candidates = array_values(array_filter($candidates, fn(string $candidate) => $candidate !== $excludeModel));
            }

            if ($candidates === []) {
                return null;
            }

            $preferredOrder = [
                'gemini-2.5-flash',
                'gemini-2.5-pro',
                'gemini-2.0-flash',
                'gemini-1.5-flash',
                'gemini-1.5-pro',
            ];

            foreach ($preferredOrder as $preferred) {
                if (\in_array($preferred, $candidates, true)) {
                    return $preferred;
                }
            }

            return $candidates[0];
        } catch (\Throwable) {
            return null;
        }
    }

    private function generate(array $contents): string
    {
        try {
            $requestPayload = [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'contents' => $contents,
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 1000],
                ],
                'timeout' => 30,
            ];

            $model = $this->currentModel();
            $url = sprintf(self::API_URL, $model, $this->apiKey);
            $response = $this->httpClient->request('POST', $url, $requestPayload);

            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);

            if ($statusCode === 200) {
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Désolé, je n'ai pas pu générer de texte.";
            }

            $errorMessage = (string)($data['error']['message'] ?? "Erreur inconnue (Code: $statusCode)");
            $retryableModelError =
                $statusCode === 404
                || $statusCode === 429
                || $statusCode === 503
                || str_contains($errorMessage, 'is not found')
                || str_contains($errorMessage, 'not supported for generateContent')
                || str_contains($errorMessage, 'currently experiencing high demand')
                || str_contains($errorMessage, 'high demand')
                || str_contains($errorMessage, 'Please try again later');

            if ($retryableModelError) {
                $fallback = $this->autoPickModel($model);
                if ($fallback && $fallback !== $model) {
                    $this->resolvedModel = $fallback;
                    $retryUrl = sprintf(self::API_URL, $fallback, $this->apiKey);
                    $retryResponse = $this->httpClient->request('POST', $retryUrl, $requestPayload);
                    $retryStatus = $retryResponse->getStatusCode();
                    $retryData = $retryResponse->toArray(false);
                    if ($retryStatus === 200) {
                        return $retryData['candidates'][0]['content']['parts'][0]['text'] ?? "Désolé, je n'ai pas pu générer de texte.";
                    }
                    $retryMessage = (string)($retryData['error']['message'] ?? "Erreur inconnue (Code: $retryStatus)");
                    throw new \RuntimeException("Erreur API Gemini : " . $retryMessage);
                }
            }

            throw new \RuntimeException("Erreur API Gemini : " . $errorMessage);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Erreur de connexion : " . $e->getMessage(), previous: $e);
        }
    }

    public function simpleChat(string $query): string
    {
        $prompt = "Tu es l'assistant GrowMind. Réponds brièvement à cette question : " . $query;
        $contents = [[
            'parts' => [['text' => $prompt]]
        ]];
        
        return $this->generate($contents);
    }

    public function recommendRessources(array $favoris, array $allRessources): array
    {
        if (empty($favoris) || empty($allRessources)) return [];

        $favTitles = array_map(fn($f) => $f->getRessource()?->getTitle(), $favoris);
        $favList = implode(', ', array_filter($favTitles));

        $catalogue = "";
        foreach (array_slice($allRessources, 0, 10) as $r) {
            $catalogue .= "ID:{$r->getId()} | Titre:{$r->getTitle()}\n";
        }

        $prompt = "Favoris: {$favList}. Catalogue: {$catalogue}. Donne 3 IDs recommandés au format JSON: {\"ids\": [1,2,3]}";
        
        try {
            $text = $this->generate([['parts' => [['text' => $prompt]]]]);
            $json = $this->extractJson($text);
            return $json['ids'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function extractJson(string $text): ?array
    {
        $text = trim($text);

        // Remove markdown code blocks
        $text = preg_replace('/```(?:json)?\s*(.*?)\s*```/is', '$1', $text);

        // Remove any leading/trailing text that isn't JSON
        $text = preg_replace('/^[^{]*/', '', $text);
        $text = preg_replace('/[^}]*$/', '', $text);

        // Try to decode directly first
        $decoded = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Try to find JSON objects with a more robust approach
        $jsonStart = strpos($text, '{');
        $jsonEnd = strrpos($text, '}');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $jsonCandidate = substr($text, $jsonStart, $jsonEnd - $jsonStart + 1);

            // Try to decode the candidate
            $decoded = json_decode($jsonCandidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Try to fix common issues
            $fixed = $this->fixCommonJsonIssues($jsonCandidate);
            if ($fixed) {
                return $fixed;
            }
        }

        return null;
    }

    private function fixCommonJsonIssues(string $json): ?array
    {
        // Remove trailing commas before closing braces/brackets
        $fixed = preg_replace('/,(\s*[}\]])/', '$1', $json);

        $decoded = json_decode($fixed, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Try to escape unescaped quotes in values
        $fixed = preg_replace('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"\s*:\s*"([^"\\\\]*(?:\\\\.[^"\\\\]*)*[^\\\\])"([^,}\]]*[^\\\\])"/', '"$1":"$2\\\\$3"', $json);

        $decoded = json_decode($fixed, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return null;
    }

    public function evaluationInsights(array $evaluations): string
    {
        if (empty($evaluations)) return "Pas d'évaluations disponibles pour l'analyse.";
        $comments = [];
        foreach ($evaluations as $e) {
            $comment = $e->getCommentaire();
            if ($comment) $comments[] = $comment;
        }
        if (empty($comments)) return "Aucun commentaire textuel à analyser.";
        
        $commentsStr = implode(" | ", array_slice($comments, 0, 20)); // Limit to avoid tokens issue
        $prompt = "Voici quelques commentaires d'utilisateurs sur nos ressources : $commentsStr. Fais un résumé du sentiment global (positif/négatif) et des points d'amélioration en 2 à 3 phrases.";
        
        return $this->generate([['parts' => [['text' => $prompt]]]]);
    }

    public function analyzePdf(string $pdfBase64): array
    {
        $prompt = 'Analyse ce document PDF et extrait un titre, une catégorie et une description courte. Réponds UNIQUEMENT avec un objet JSON valide au format exact suivant, sans aucun texte avant ou après :

{"title": "Titre du document", "category": "Catégorie principale", "description": "Description courte du contenu"}';

        try {
            $model = $this->currentModel();
            $url = sprintf(self::API_URL, $model, $this->apiKey);
            $response = $this->httpClient->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'contents' => [[
                        'parts' => [
                            ['text' => $prompt],
                            ['inline_data' => ['mime_type' => 'application/pdf', 'data' => $pdfBase64]]
                        ]
                    ]],
                    'generationConfig' => ['temperature' => 0.1] // Lower temperature for more consistent output
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray(false);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $json = $this->extractJson($text);

            if ($json && isset($json['title'], $json['category'], $json['description'])) {
                return ['success' => true, 'data' => $json];
            }

            // Fallback: try to extract basic information even if JSON is malformed
            $fallback = $this->extractFallbackInfo($text);
            if ($fallback) {
                return ['success' => true, 'data' => $fallback];
            }

            return ['success' => false, 'error' => "Format JSON invalide renvoyé par l'IA.", 'raw' => $text];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function extractFallbackInfo(string $text): ?array
    {
        // Try to extract title, category, and description using regex patterns
        $title = null;
        $category = null;
        $description = null;

        // Look for patterns like "Titre: ..." or "Title: ..."
        if (preg_match('/(?:titre|title)\s*:\s*([^\n\r]+)/i', $text, $matches)) {
            $title = trim($matches[1], '"\'');
        }

        // Look for patterns like "Catégorie: ..." or "Category: ..."
        if (preg_match('/(?:catégorie|category)\s*:\s*([^\n\r]+)/i', $text, $matches)) {
            $category = trim($matches[1], '"\'');
        }

        // Look for patterns like "Description: ..."
        if (preg_match('/(?:description)\s*:\s*([^\n\r]+)/i', $text, $matches)) {
            $description = trim($matches[1], '"\'');
        }

        if ($title && $category && $description) {
            return [
                'title' => $title,
                'category' => $category,
                'description' => $description
            ];
        }

        return null;
    }
}
