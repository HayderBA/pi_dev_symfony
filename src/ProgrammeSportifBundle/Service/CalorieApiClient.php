<?php

namespace App\ProgrammeSportifBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CalorieApiClient
{
    private const METS = [
        'walking' => 3.8,
        'running' => 8.3,
        'cycling' => 7.5,
        'yoga' => 2.5,
        'swimming' => 6.0,
        'strength training' => 6.0,
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $endpoint,
        private readonly string $apiKey,
    ) {
    }

    /**
     * @return array{activity: string, totalCalories: int, duration: int, source: string}
     */
    public function estimate(string $activity, float $weightKg, int $durationMinutes): array
    {
        $fallback = $this->buildFallbackEstimate($activity, $weightKg, $durationMinutes);

        if (! $this->isConfigured()) {
            return $fallback;
        }

        try {
            $response = $this->httpClient->request('GET', $this->endpoint, [
                'query' => [
                    'activity' => $activity,
                    'weight' => (int) round($weightKg * 2.20462),
                    'duration' => $durationMinutes,
                ],
                'headers' => [
                    'X-Api-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ],
                'timeout' => 8,
            ]);

            $payload = $response->toArray(false);
            $firstResult = isset($payload[0]) && is_array($payload[0]) ? $payload[0] : [];

            if ([] === $firstResult) {
                return $fallback;
            }

            $totalCalories = isset($firstResult['total_calories']) && is_numeric($firstResult['total_calories'])
                ? (int) round((float) $firstResult['total_calories'])
                : $fallback['totalCalories'];

            return [
                'activity' => (string) ($firstResult['name'] ?? $this->humanizeActivity($activity)),
                'totalCalories' => $totalCalories,
                'duration' => $durationMinutes,
                'source' => 'API Ninjas Calories Burned',
            ];
        } catch (\Throwable $exception) {
            $this->logger->warning('Calorie API fallback engaged.', [
                'message' => $exception->getMessage(),
            ]);

            return $fallback;
        }
    }

    private function isConfigured(): bool
    {
        return '' !== trim($this->endpoint) && '' !== trim($this->apiKey);
    }

    /**
     * @return array{activity: string, totalCalories: int, duration: int, source: string}
     */
    private function buildFallbackEstimate(string $activity, float $weightKg, int $durationMinutes): array
    {
        $met = self::METS[$activity] ?? self::METS['walking'];
        $estimatedCalories = (int) round(($met * 3.5 * $weightKg / 200) * $durationMinutes);

        return [
            'activity' => $this->humanizeActivity($activity),
            'totalCalories' => $estimatedCalories,
            'duration' => $durationMinutes,
            'source' => 'Calcul local',
        ];
    }

    private function humanizeActivity(string $activity): string
    {
        return match ($activity) {
            'walking' => 'marche rapide',
            'running' => 'jogging',
            'cycling' => 'cyclisme',
            'yoga' => 'yoga',
            'swimming' => 'natation',
            'strength training' => 'renforcement',
            default => str_replace('_', ' ', $activity),
        };
    }
}
