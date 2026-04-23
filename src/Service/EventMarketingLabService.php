<?php

namespace App\Service;

use App\Entity\Evenement;

class EventMarketingLabService
{
    /**
     * @param Evenement[] $events
     */
    public function buildMarketingExperience(array $events): array
    {
        $campaigns = [];
        $eventMeta = [];

        foreach ($events as $event) {
            if (!$event instanceof Evenement) {
                continue;
            }

            $event->updateDynamicPrice();
            $occupancy = $event->getOccupancyRate();
            $remaining = $event->getPlacesRestantes();
            $theme = $this->detectTheme($event);
            $campaignKeys = [];

            if ($remaining >= 2 && $event->getStatus() !== 'Passe') {
                $campaignKeys[] = 'duo';
            }

            if ($remaining > 0 && $occupancy <= 55 && $event->getStatus() !== 'Passe') {
                $campaignKeys[] = 'discovery';
            }

            if ($occupancy >= 70 || $event->isDynamicPriceActiveBool()) {
                $campaignKeys[] = 'hot';
            }

            if ($theme === 'therapy' || $theme === 'wellness') {
                $campaignKeys[] = 'sponsor';
            }

            $eventMeta[$event->getId()] = [
                'campaigns' => array_values(array_unique($campaignKeys)),
                'sponsor' => $this->buildSponsorName($theme, $event->getId() ?? 0),
                'hook' => $this->buildHook($theme, $occupancy, $remaining),
                'offer' => $this->buildOfferLabel($campaignKeys, $theme),
                'theme' => $theme,
            ];
        }

        $campaigns[] = [
            'key' => 'duo',
            'title' => 'Duo Boost',
            'subtitle' => 'Offre sociale et apaisante',
            'description' => 'Mettez en avant les evenements avec assez de places pour venir a deux et transformer la reservation en moment partage.',
            'accent' => 'lime',
            'cta' => 'Voir les offres duo',
            'icon' => 'fas fa-user-friends',
        ];
        $campaigns[] = [
            'key' => 'discovery',
            'title' => 'Discovery Pulse',
            'subtitle' => 'Mise en avant decouverte',
            'description' => 'Reperez les experiences qui meritent un boost publicitaire intelligent pour augmenter leur traction sans changer le prix reel.',
            'accent' => 'blue',
            'cta' => 'Explorer les decouvertes',
            'icon' => 'fas fa-bolt',
        ];
        $campaigns[] = [
            'key' => 'hot',
            'title' => 'Hot Momentum',
            'subtitle' => 'Urgence premium',
            'description' => 'Faites ressortir les evenements a forte demande avec une animation plus nerveuse, des badges rares et un sentiment d exclusivite.',
            'accent' => 'orange',
            'cta' => 'Afficher les evenements chauds',
            'icon' => 'fas fa-fire',
        ];
        $campaigns[] = [
            'key' => 'sponsor',
            'title' => 'Sponsor Spotlight',
            'subtitle' => 'Ancrage therapeutique',
            'description' => 'Ajoutez une couche de sponsoring thematique autour du yoga, du mental wellness et des experiences psycho-corporelles.',
            'accent' => 'violet',
            'cta' => 'Activer le spotlight',
            'icon' => 'fas fa-star',
        ];

        return [
            'campaigns' => $campaigns,
            'eventMeta' => $eventMeta,
            'videoAds' => [
                [
                    'title' => 'Flow Yoga Morning',
                    'tag' => 'Sponsor wellness',
                    'description' => 'Une capsule video zen pour promouvoir les ateliers respiration, yoga doux et recentrage.',
                    'embedUrl' => 'https://www.youtube.com/embed/4pKly2JojMw?rel=0',
                ],
                [
                    'title' => 'Mindful Sport Energy',
                    'tag' => 'Activation sport',
                    'description' => 'Une ambiance plus active pour porter les evenements cardio doux, posture et energie positive.',
                    'embedUrl' => 'https://www.youtube.com/embed/ml6cT4AZdqI?rel=0',
                ],
                [
                    'title' => 'Therapy Calm Space',
                    'tag' => 'Focus therapie',
                    'description' => 'Un univers video enveloppant pour les experiences psycho-educatives, relaxation et sante mentale.',
                    'embedUrl' => 'https://www.youtube.com/embed/ZToicYcHIOU?rel=0',
                ],
            ],
        ];
    }

    private function detectTheme(Evenement $event): string
    {
        $text = mb_strtolower(trim(($event->getTitre() ?? '') . ' ' . ($event->getDescription() ?? '')));

        if (str_contains($text, 'yoga') || str_contains($text, 'pilates') || str_contains($text, 'respiration')) {
            return 'wellness';
        }

        if (str_contains($text, 'therapy') || str_contains($text, 'therapie') || str_contains($text, 'psy') || str_contains($text, 'stress')) {
            return 'therapy';
        }

        if (str_contains($text, 'sport') || str_contains($text, 'fitness') || str_contains($text, 'marche')) {
            return 'sport';
        }

        return 'wellness';
    }

    private function buildSponsorName(string $theme, int $seed): string
    {
        $names = match ($theme) {
            'therapy' => ['TheraPulse', 'MindNest', 'CalmBridge'],
            'sport' => ['MoveSpark', 'PulseLab', 'ActiveNest'],
            default => ['ZenWave', 'BreathBloom', 'AuraFlow'],
        };

        return $names[$seed % count($names)];
    }

    private function buildHook(string $theme, float $occupancy, int $remaining): string
    {
        if ($occupancy >= 80) {
            return 'Edition tres demandee, mettez l accent sur la rarete et la reservation rapide.';
        }

        if ($remaining <= 12) {
            return 'Peu de places restantes, excellent terrain pour une campagne d urgence douce.';
        }

        return match ($theme) {
            'therapy' => 'Positionnez cette experience comme une pause mentale premium et rassurante.',
            'sport' => 'Mettez en avant l energie collective, la progression et le challenge doux.',
            default => 'Ideal pour une narration wellbeing, respiration et reset emotionnel.',
        };
    }

    private function buildOfferLabel(array $campaignKeys, string $theme): string
    {
        if (in_array('hot', $campaignKeys, true)) {
            return 'Momentum fort';
        }

        if (in_array('duo', $campaignKeys, true)) {
            return 'Pack duo suggere';
        }

        if ($theme === 'therapy') {
            return 'Spotlight therapie';
        }

        return 'Boost decouverte';
    }
}
