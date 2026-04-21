<?php

namespace App\Service;

final class IMCApiService
{
    /**
     * @return array{imc: float, categorie: string}
     */
    public function calculate(float $poids, float $taille): array
    {
        if ($poids <= 0 || $taille <= 0) {
            throw new \InvalidArgumentException('Le poids et la taille doivent etre superieurs a zero.');
        }

        $imc = round($poids / ($taille * $taille), 1);

        return [
            'imc' => $imc,
            'categorie' => $this->resolveCategory($imc),
        ];
    }

    private function resolveCategory(float $imc): string
    {
        return match (true) {
            $imc < 18.5 => 'maigre',
            $imc < 25 => 'normal',
            default => 'surpoids',
        };
    }
}
