<?php

namespace App\Service;

use App\Entity\Evenement;

class EventImpactService
{
    public function getPartners(): array
    {
        return [
            ['name' => 'Association Beity', 'city' => 'Tunis', 'focus' => 'Protection et accompagnement des femmes', 'badge' => 'Impact social'],
            ['name' => 'Association Tunisienne SOS Village Enfants', 'city' => 'Tunis', 'focus' => 'Soutien a l enfance et aux familles', 'badge' => 'Enfance'],
            ['name' => 'Psychologues du Coeur', 'city' => 'Tunis', 'focus' => 'Ecoute et soutien psychologique', 'badge' => 'Sante mentale'],
            ['name' => 'Croissant Rouge Tunisien', 'city' => 'Tunis', 'focus' => 'Solidarite communautaire et urgence', 'badge' => 'Solidarite'],
            ['name' => 'Association Basma', 'city' => 'Ariana', 'focus' => 'Inclusion et accompagnement de personnes vulnerables', 'badge' => 'Inclusion'],
            ['name' => 'WeYouth Tunisia', 'city' => 'Tunis', 'focus' => 'Engagement des jeunes et actions citoyennes', 'badge' => 'Jeunesse'],
            ['name' => 'Tunisie Reboisement', 'city' => 'Tunis', 'focus' => 'Actions vertes et mieux-vivre urbain', 'badge' => 'Ecologie'],
            ['name' => 'Tounes Clean-Up', 'city' => 'La Marsa', 'focus' => 'Actions locales et mobilisation citoyenne', 'badge' => 'Communaute'],
            ['name' => 'Association Amal pour la Famille', 'city' => 'Tunis', 'focus' => 'Accompagnement psychosocial des familles', 'badge' => 'Famille'],
            ['name' => 'Je Veux Aider', 'city' => 'Tunis', 'focus' => 'Mise en relation dons, benevolat et aide de proximite', 'badge' => 'Humanitaire'],
        ];
    }

    /**
     * @param Evenement[] $events
     */
    public function buildForEvents(array $events): array
    {
        $partners = $this->getPartners();
        $meta = [];

        foreach ($events as $index => $event) {
            if (!$event instanceof Evenement || $event->getId() === null) {
                continue;
            }

            $partner = $this->matchPartner($event, $partners, $index);
            $amounts = $this->suggestAmounts($event);

            $meta[$event->getId()] = [
                'partner' => $partner,
                'amounts' => $amounts,
                'hook' => $this->buildHook($event, $partner),
                'impactLabel' => $this->buildImpactLabel($event),
            ];
        }

        return [
            'partners' => $partners,
            'meta' => $meta,
        ];
    }

    public function getForEvent(Evenement $event): array
    {
        $data = $this->buildForEvents([$event]);

        return $data['meta'][$event->getId()] ?? [
            'partner' => $this->getPartners()[0],
            'amounts' => [10, 20, 30],
            'hook' => 'Ajoutez un geste solidaire a votre reservation.',
            'impactLabel' => 'Billet solidaire',
        ];
    }

    private function matchPartner(Evenement $event, array $partners, int $index): array
    {
        $text = mb_strtolower(($event->getTitre() ?? '') . ' ' . ($event->getDescription() ?? ''));

        foreach ($partners as $offset => $partner) {
            if (
                (str_contains($text, 'psy') || str_contains($text, 'stress') || str_contains($text, 'therapie') || str_contains($text, 'mental')) &&
                in_array($partner['badge'], ['Sante mentale', 'Famille'], true)
            ) {
                return $partner;
            }

            if (
                (str_contains($text, 'yoga') || str_contains($text, 'pilates') || str_contains($text, 'respiration')) &&
                in_array($partner['badge'], ['Communaute', 'Ecologie', 'Bien-etre'], true)
            ) {
                return $partner;
            }

            if (
                (str_contains($text, 'sport') || str_contains($text, 'fitness') || str_contains($text, 'marche')) &&
                in_array($partner['badge'], ['Jeunesse', 'Solidarite', 'Communaute'], true)
            ) {
                return $partner;
            }
        }

        return $partners[$index % count($partners)];
    }

    private function suggestAmounts(Evenement $event): array
    {
        if ($event->isDynamicPriceActiveBool() || $event->getOccupancyRate() >= 70) {
            return [10, 20, 30];
        }

        if ($event->getDynamicPrice() >= 80) {
            return [10, 30, 50];
        }

        return [5, 10, 20];
    }

    private function buildHook(Evenement $event, array $partner): string
    {
        if ($event->getOccupancyRate() >= 75) {
            return sprintf('Cet evenement attire deja du monde. Ajoutez un petit don pour amplifier l impact de %s.', $partner['name']);
        }

        return sprintf('Votre place peut aussi soutenir %s a %s, autour de %s.', $partner['name'], $partner['city'], mb_strtolower($partner['focus']));
    }

    private function buildImpactLabel(Evenement $event): string
    {
        $text = mb_strtolower(($event->getTitre() ?? '') . ' ' . ($event->getDescription() ?? ''));

        if (str_contains($text, 'psy') || str_contains($text, 'stress') || str_contains($text, 'therapie')) {
            return 'Impact sante mentale';
        }

        if (str_contains($text, 'yoga') || str_contains($text, 'pilates')) {
            return 'Impact bien-etre';
        }

        return 'Impact communaute';
    }
}
