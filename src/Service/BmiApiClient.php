<?php

namespace App\Service;

final class BmiApiClient
{
    /**
     * @return array{imc: float, categorie: string, source: string}
     */
    public function calculate(float $weightKg, float $heightMeters): array
    {
        $heightM = max($heightMeters, 0.1);
        $bmi = round($weightKg / ($heightM * $heightM), 1);

        $category = match (true) {
            $bmi < 18.5 => 'Sous poids',
            $bmi < 25 => 'Normal',
            $bmi < 30 => 'Surpoids',
            default => 'Obesite',
        };

        return [
            'imc' => $bmi,
            'categorie' => $category,
            'source' => 'Calcul local',
        ];
    }

    /**
     * @return array{bmi: float, category: string, dailyCalories: int, summary: string, source: string}
     */
    public function analyze(float $weightKg, float $heightCm, int $age, string $gender, string $activityLevel): array
    {
        $heightM = max($heightCm / 100, 0.1);
        $bmi = round($weightKg / ($heightM * $heightM), 1);

        $category = match (true) {
            $bmi < 18.5 => 'Sous poids',
            $bmi < 25 => 'Normal',
            $bmi < 30 => 'Surpoids',
            default => 'Obesite',
        };

        $bmr = 'female' === $gender
            ? (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) - 161
            : (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) + 5;

        $multiplier = match ($activityLevel) {
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'veryactive' => 1.9,
            default => 1.45,
        };

        $dailyCalories = (int) round($bmr * $multiplier);

        return [
            'bmi' => $bmi,
            'category' => $category,
            'dailyCalories' => $dailyCalories,
            'summary' => sprintf(
                'IMC estime a %.1f (%s) avec un besoin calorique journalier autour de %d kcal.',
                $bmi,
                strtolower($category),
                $dailyCalories
            ),
            'source' => 'Calcul local',
        ];
    }
}
