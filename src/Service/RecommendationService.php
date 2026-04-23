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
        $needsRestSupport = $this->isOneOf($normalizedMood, ['fatigue', 'stresse', 'stressee', 'stress', 'moyen']);

        if ($isHighStress && $isLowSleepQuality && $needsRestSupport) {
            $recommendations[] = 'Votre stress, votre sommeil et votre humeur montrent une surcharge. Levez le pied aujourd hui et prevoyez une routine du soir tres calme.';
        }

        if ($isHighStress) {
            $recommendations[] = 'Essayez une respiration lente, une courte marche ou 10 minutes de meditation pour faire redescendre la tension.';
        }

        if ($isLowSleepQuality) {
            $recommendations[] = 'Ameliorez votre hygiene du sommeil: horaires reguliers, moins d ecrans avant le coucher et chambre plus calme.';
        }

        if ($needsRestSupport) {
            $recommendations[] = 'Ajoutez une vraie pause de recuperation dans la journee et reduisez les taches les plus exigeantes.';
        }

        if (!$isHighStress && !$isLowSleepQuality && $this->isOneOf($normalizedMood, ['calme', 'bien', 'motive', 'motivé', 'heureux'])) {
            $recommendations[] = 'Vos indicateurs sont plutot bons. Continuez vos habitudes utiles: sommeil stable, activite douce et moments de recentrage.';
        }

        if ([] === $recommendations) {
            return 'Continuez vos habitudes actuelles et gardez un rythme de vie regulier.';
        }

        return implode(' ', array_unique($recommendations));
    }

    private function normalizeMood(?string $mood): string
    {
        $mood = trim((string) $mood);
        if ('' === $mood) {
            return '';
        }

        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $mood);
        if (false !== $ascii) {
            $mood = $ascii;
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
