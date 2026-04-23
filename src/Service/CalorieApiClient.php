<?php

namespace App\Service;

final class CalorieApiClient
{
    /**
     * @return array{calories: int, source: string, error: ?string, items: array<int, array{name: string, calories: float}>}
     */
    public function analyzeNutritionQuery(string $query): array
    {
        $catalog = [
            'apple' => 95.0,
            'banana' => 105.0,
            'sandwich' => 130.3,
            'soda' => 159.7,
            'water' => 0.0,
            'coffee' => 5.0,
            'tea' => 2.0,
            'pizza' => 285.0,
            'salad' => 85.0,
            'burger' => 295.0,
        ];

        $normalized = strtolower(trim($query));
        $parts = preg_split('/\s*(?:,|and|et)\s*/', $normalized) ?: [];
        $items = [];
        $error = null;

        foreach ($parts as $part) {
            $part = trim($part);
            if ('' === $part) {
                continue;
            }

            $quantity = 1.0;
            $label = $part;

            if (preg_match('/^([0-9]+(?:[.,][0-9]+)?)\s+(.+)$/', $part, $matches)) {
                $quantity = (float) str_replace(',', '.', $matches[1]);
                $label = trim($matches[2]);
            }

            $label = preg_replace('/\s+/', ' ', $label ?? '');
            $calories = $catalog[$label] ?? 120.0;

            if (!isset($catalog[$label]) && null === $error) {
                $error = 'Certains aliments n ont pas ete trouves exactement dans le catalogue local.';
            }

            $items[] = [
                'name' => $label,
                'calories' => round($calories * $quantity, 1),
            ];
        }

        $total = (int) round(array_reduce($items, static function (float $sum, array $item): float {
            return $sum + (float) $item['calories'];
        }, 0.0));

        return [
            'calories' => $total,
            'source' => 'api',
            'error' => $error,
            'items' => $items,
        ];
    }

    /**
     * @return array{activity: string, totalCalories: int, duration: int, source: string}
     */
    public function estimate(string $activity, float $weightKg, int $duration): array
    {
        $met = match ($activity) {
            'running' => 9.8,
            'cycling' => 7.5,
            'swimming' => 7.0,
            'strength training' => 5.5,
            'yoga' => 3.0,
            default => 4.3,
        };

        $hours = max($duration, 1) / 60;
        $totalCalories = (int) round($met * $weightKg * $hours);

        return [
            'activity' => $activity,
            'totalCalories' => $totalCalories,
            'duration' => $duration,
            'source' => 'Calcul local',
        ];
    }
}
