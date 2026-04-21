<?php

namespace App\ProgrammeSportifBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class BmiApiClient
{
    private const ACTIVITY_MULTIPLIERS = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'veryactive' => 1.9,
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $endpoint,
        private readonly string $apiKey,
    ) {
    }

    /**
     * @return array{bmi: float, category: string, dailyCalories: int, summary: string, source: string}
     */
    public function analyze(
        float $weightKg,
        float $heightCm,
        int $age,
        string $gender,
        string $activityLevel,
    ): array {
        $fallback = $this->buildFallbackProfile($weightKg, $heightCm, $age, $gender, $activityLevel);

        if (! $this->isConfigured()) {
            return $fallback;
        }

        try {
            $response = $this->httpClient->request('GET', $this->endpoint, [
                'query' => [
                    'weight' => $weightKg,
                    'height' => $heightCm,
                    'unit' => 'metric',
                    'age' => $age,
                    'gender' => $gender,
                    'activityLevel' => $activityLevel,
                ],
                'headers' => [
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ],
                'timeout' => 8,
            ]);

            $payload = $response->toArray(false);
            $data = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : [];

            if ('ok' !== ($payload['status'] ?? '')) {
                return $fallback;
            }

            $bmi = isset($data['bmi']) && is_numeric($data['bmi'])
                ? round((float) $data['bmi'], 1)
                : $fallback['bmi'];

            $dailyCalories = $this->extractDailyCalories($data) ?? $fallback['dailyCalories'];
            $category = $this->resolveCategory($bmi);

            return [
                'bmi' => $bmi,
                'category' => $category,
                'dailyCalories' => $dailyCalories,
                'summary' => $this->buildSummary($bmi, $category, $dailyCalories),
                'source' => 'APIVerve BMI API',
            ];
        } catch (\Throwable $exception) {
            $this->logger->warning('BMI API fallback engaged.', [
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
     * @return array{bmi: float, category: string, dailyCalories: int, summary: string, source: string}
     */
    private function buildFallbackProfile(
        float $weightKg,
        float $heightCm,
        int $age,
        string $gender,
        string $activityLevel,
    ): array {
        $heightMeters = max($heightCm / 100, 0.1);
        $bmi = round($weightKg / ($heightMeters * $heightMeters), 1);
        $category = $this->resolveCategory($bmi);
        $dailyCalories = $this->estimateDailyCalories($weightKg, $heightCm, $age, $gender, $activityLevel);

        return [
            'bmi' => $bmi,
            'category' => $category,
            'dailyCalories' => $dailyCalories,
            'summary' => $this->buildSummary($bmi, $category, $dailyCalories),
            'source' => 'Calcul local',
        ];
    }

    private function estimateDailyCalories(
        float $weightKg,
        float $heightCm,
        int $age,
        string $gender,
        string $activityLevel,
    ): int {
        $base = ('female' === $gender ? -161 : 5) + (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age);
        $multiplier = self::ACTIVITY_MULTIPLIERS[$activityLevel] ?? self::ACTIVITY_MULTIPLIERS['moderate'];

        return (int) round($base * $multiplier);
    }

    private function resolveCategory(float $bmi): string
    {
        return match (true) {
            $bmi < 18.5 => 'Sous poids',
            $bmi < 25 => 'Normal',
            $bmi < 30 => 'Surpoids',
            default => 'Obesite',
        };
    }

    private function buildSummary(float $bmi, string $category, int $dailyCalories): string
    {
        return sprintf(
            'IMC estime a %.1f (%s) avec un besoin calorique journalier autour de %d kcal.',
            $bmi,
            strtolower($category),
            $dailyCalories
        );
    }

    private function extractDailyCalories(array $data): ?int
    {
        $candidateKeys = [
            'tdee',
            'dailyCalories',
            'daily_calories',
            'maintenanceCalories',
            'maintenance_calories',
            'calorieTarget',
            'calorie_target',
            'calories',
        ];

        foreach ($candidateKeys as $candidateKey) {
            $value = $data[$candidateKey] ?? null;

            if (is_numeric($value)) {
                return (int) round((float) $value);
            }

            if (is_array($value)) {
                foreach (['maintain', 'maintenance', 'target', 'recommended'] as $nestedKey) {
                    if (isset($value[$nestedKey]) && is_numeric($value[$nestedKey])) {
                        return (int) round((float) $value[$nestedKey]);
                    }
                }
            }
        }

        return null;
    }
}
