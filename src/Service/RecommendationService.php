<?php

namespace App\Service;

final class RecommendationService
{
    public function generateRecommendations(?int $stressLevel, ?int $sleepQuality, ?string $mood): string
    {
        $normalizedMood = $this->normalizeMood($mood);
        $recommendations = [];
        $isHighStress = null !== $stressLevel && $stressLevel > 7;
        $isLowSleepQuality = null !== $sleepQuality && $sleepQuality < 5;
        $needsRestSupport = $this->isOneOf($normalizedMood, ['fatigue', 'stresse']);

        if ($isHighStress && $isLowSleepQuality && $needsRestSupport) {
            $recommendations[] = 'Your stress, sleep, and mood suggest a clear overload. Reduce non-essential tasks today, plan a calm evening routine, and prioritize deep rest.';
        }

        if ($isHighStress && $isLowSleepQuality) {
            $recommendations[] = 'High stress combined with poor sleep points to recovery debt. Focus on a lighter schedule, hydration, and a screen-free wind-down before bed.';
        }

        if ($isHighStress && $needsRestSupport) {
            $recommendations[] = 'Your current mood and stress level indicate mental tension. Try 10 minutes of breathing exercises, meditation, or a quiet break before continuing demanding work.';
        }

        if ($isLowSleepQuality && $needsRestSupport) {
            $recommendations[] = 'Low sleep quality appears to be affecting your energy and mood. Aim for an earlier bedtime, a darker room, and a slower morning pace if possible.';
        }

        if ($isHighStress) {
            $recommendations[] = 'Try relaxation techniques such as deep breathing, stretching, or a short walk.';
        }

        if ($isLowSleepQuality) {
            $recommendations[] = 'Improve your sleep habits by keeping a regular schedule and limiting screens before bedtime.';
        }

        if ($needsRestSupport) {
            $recommendations[] = 'Take time for meditation and rest to recover energy and reduce mental pressure.';
        }

        if (! $isHighStress && ! $isLowSleepQuality && $this->isOneOf($normalizedMood, ['calme', 'detendu', 'heureux', 'bien'])) {
            $recommendations[] = 'Your current signals look balanced. Maintain the habits that support your calm mood, including regular sleep and short recovery breaks.';
        }

        if ([] === $recommendations) {
            return 'Keep maintaining your current well-being habits.';
        }

        return implode(' ', array_unique($recommendations));
    }

    private function normalizeMood(?string $mood): string
    {
        $mood = trim((string) $mood);

        if ('' === $mood) {
            return '';
        }

        $asciiMood = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $mood);

        if (false !== $asciiMood) {
            $mood = $asciiMood;
        }

        return strtolower($mood);
    }

    /**
     * @param string[] $expectedValues
     */
    private function isOneOf(string $value, array $expectedValues): bool
    {
        return '' !== $value && in_array($value, $expectedValues, true);
    }
}
