<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CaloriesApiService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly string $endpoint,
        private readonly string $apiKey,
        private readonly int $defaultCalories,
    ) {
    }

    public function getRecommendedCalories(string $nutritionQuery): int
    {
        return $this->fetchNutritionData($nutritionQuery)['calories'];
    }

    /**
     * @return array{calories: int, source: string, items: array<int, array{name: string, calories: float}>, error: ?string}
     */
    public function fetchNutritionData(string $nutritionQuery): array
    {
        $nutritionQuery = trim($nutritionQuery);

        if ('' === $nutritionQuery) {
            return $this->getDefaultResponse('Requete nutritionnelle vide.');
        }

        if (! $this->isConfigured()) {
            return $this->getDefaultResponse($this->getMissingConfigurationMessage());
        }

        try {
            $response = $this->client->request('GET', $this->endpoint, [
                'headers' => [
                    'X-Api-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'query' => $nutritionQuery,
                ],
                'timeout' => 8,
            ]);

            $statusCode = $response->getStatusCode();
            $payload = $response->toArray(false);

            if ($statusCode < 200 || $statusCode >= 300) {
                return $this->getDefaultResponse($this->buildApiErrorMessage($statusCode, $payload));
            }

            if (! is_array($payload) || empty($payload)) {
                return $this->getDefaultResponse('Reponse API invalide.');
            }

            $items = [];
            $totalCalories = 0.0;

            foreach ($payload as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $calories = $this->resolveItemCaloriesKcal($item);

                $items[] = [
                    'name' => (string) ($item['name'] ?? 'aliment'),
                    'calories' => round($calories, 1),
                ];

                $totalCalories += $calories;
            }

            if ($totalCalories <= 0) {
                return $this->getDefaultResponse('Calories introuvables dans la reponse API.');
            }

            return [
                'calories' => (int) round($totalCalories),
                'source' => 'api',
                'items' => $items,
                'error' => null,
            ];
        } catch (TransportExceptionInterface $exception) {
            $this->logger->warning('Calories API fallback engaged after transport failure.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->getDefaultResponse('Connexion a API Ninjas impossible. Verifie la connectivite reseau.');
        } catch (\Throwable $exception) {
            $this->logger->warning('Calories API fallback engaged.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->getDefaultResponse('Echec de l appel API.');
        }
    }

    private function isConfigured(): bool
    {
        return '' !== trim($this->endpoint)
            && ! $this->isPlaceholderValue($this->apiKey);
    }

    private function getMissingConfigurationMessage(): string
    {
        return 'Configuration API absente. Renseigne API_NINJAS_KEY dans .env.local.';
    }

    /**
     * @return array{calories: int, source: string, items: array<int, array{name: string, calories: float}>, error: ?string}
     */
    private function getDefaultResponse(?string $error = null): array
    {
        return [
            'calories' => $this->defaultCalories,
            'source' => 'fallback',
            'items' => [],
            'error' => $error,
        ];
    }

    private function isPlaceholderValue(string $value): bool
    {
        $normalizedValue = trim($value);

        if ('' === $normalizedValue) {
            return true;
        }

        return str_starts_with($normalizedValue, 'PASTE_YOUR_')
            || str_contains($normalizedValue, 'YOUR_API_NINJAS');
    }

    private function buildApiErrorMessage(int $statusCode, mixed $payload): string
    {
        $apiMessage = $this->extractPayloadMessage($payload);

        if (401 === $statusCode || 403 === $statusCode) {
            if (null !== $apiMessage) {
                return sprintf(
                        'Erreur API Ninjas (%d): %s. Verifie API_NINJAS_KEY.',
                    $statusCode,
                    $apiMessage
                );
            }

            return sprintf(
                    'Erreur API Ninjas (%d): acces refuse. Verifie API_NINJAS_KEY.',
                $statusCode
            );
        }

        if (429 === $statusCode) {
            return sprintf('Erreur API Ninjas (%d): limite API atteinte. Reessaie plus tard.', $statusCode);
        }

        if (null !== $apiMessage) {
            return sprintf('Erreur API Ninjas (%d): %s', $statusCode, $apiMessage);
        }

        return sprintf('Erreur API Ninjas (%d).', $statusCode);
    }

    private function extractPayloadMessage(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach (['message', 'error', 'detail'] as $key) {
            if (isset($payload[$key]) && is_string($payload[$key]) && '' !== trim($payload[$key])) {
                return trim($payload[$key]);
            }
        }

        return null;
    }

    /**
     * API Ninjas peut renvoyer HTTP 200 avec "calories" / "protein_g" non numeriques sur le plan gratuit.
     * On applique alors une estimation Atwater classique a partir des macronutriments disponibles.
     */
    private function resolveItemCaloriesKcal(array $item): float
    {
        if (isset($item['calories']) && is_numeric($item['calories'])) {
            return (float) $item['calories'];
        }

        $proteinG = $this->readNumericGramField($item, 'protein_g');
        $carbsG = $this->readNumericGramField($item, 'carbohydrates_total_g');
        $fatG = $this->readNumericGramField($item, 'fat_total_g');

        return 4.0 * $proteinG + 4.0 * $carbsG + 9.0 * $fatG;
    }

    private function readNumericGramField(array $item, string $field): float
    {
        if (! isset($item[$field]) || ! is_numeric($item[$field])) {
            return 0.0;
        }

        return (float) $item[$field];
    }
}
