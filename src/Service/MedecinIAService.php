<?php

namespace App\Service;

class MedecinIAService
{
    public function getReponse(string $message): string
    {
        $message = mb_strtolower(trim($message));

        if (str_contains($message, 'stress') || str_contains($message, 'anxiete') || str_contains($message, 'anxiété')) {
            return "Respirez doucement cinq fois. Inspirez par le nez, expirez lentement. Voulez-vous une autre technique simple ?";
        }

        if (str_contains($message, 'triste') || str_contains($message, 'deprime') || str_contains($message, 'déprime')) {
            return "Je comprends. Parler peut déjà aider. Si vous voulez, dites-moi ce qui vous pèse le plus aujourd'hui.";
        }

        if (str_contains($message, 'dormir') || str_contains($message, 'insomnie') || str_contains($message, 'sommeil')) {
            return "Essayez une heure fixe pour dormir, moins d'écran avant le coucher et une respiration calme pendant deux minutes.";
        }

        if (str_contains($message, 'fatigue')) {
            return "La fatigue peut venir du stress ou du sommeil. Hydratez-vous, reposez-vous un peu et surveillez si cela dure.";
        }

        if (str_contains($message, 'confiance') || str_contains($message, 'estime')) {
            return "Notez aujourd'hui une petite réussite personnelle. Ce geste simple aide à reconstruire la confiance progressivement.";
        }

        if (str_contains($message, 'seul') || str_contains($message, 'solitude')) {
            return "Vous n'êtes pas seul. Le forum GrowMind et la carte des médecins peuvent vous aider à reprendre contact et demander du soutien.";
        }

        if (str_contains($message, 'rdv') || str_contains($message, 'rendez-vous') || str_contains($message, 'docteur')) {
            return "Vous pouvez utiliser les pages médecins, forum et SOS selon votre besoin. Si c'est urgent, utilisez le bouton SOS.";
        }

        if (str_contains($message, 'bonjour') || str_contains($message, 'salut')) {
            return "Bonjour. Je suis l'assistant GrowMind. Dites-moi ce qui vous préoccupe et je vais vous orienter simplement.";
        }

        return "Je peux vous aider sur le stress, le sommeil, la tristesse, l'urgence ou l'orientation vers un médecin. Dites-m'en un peu plus.";
    }

    public function isUrgent(string $message): bool
    {
        $message = mb_strtolower($message);
        $urgentMots = ['suicide', 'mourir', 'urgence vitale', 'hopital', 'ambulance', 'détresse', 'detresse'];

        foreach ($urgentMots as $mot) {
            if (str_contains($message, $mot)) {
                return true;
            }
        }

        return false;
    }
}
