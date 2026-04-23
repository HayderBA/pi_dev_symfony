<?php

namespace App\Service;

use App\Entity\ProgrammeSportif;

final class ProgrammeSportifGenerator
{
    public function __construct(
        private readonly BmiApiClient $bmiApiClient,
        private readonly CalorieApiClient $calorieApiClient,
    ) {
    }

    public function generate(ProgrammeSportif $programme): void
    {
        $bmiProfile = $this->bmiApiClient->analyze(
            $programme->getPoidsKg() ?? 0.0,
            $programme->getTailleCm() ?? 0.0,
            $programme->getAge() ?? 0,
            $programme->getGenre() ?? 'male',
            $programme->getNiveauActivite() ?? 'moderate',
        );

        $template = $this->selectTemplate($programme, $bmiProfile['bmi']);
        $calorieProfile = $this->calorieApiClient->estimate(
            $template['referenceActivity'],
            $programme->getPoidsKg() ?? 0.0,
            $template['referenceDuration'],
        );

        $programme
            ->setImc($bmiProfile['bmi'])
            ->setCategorieImc($bmiProfile['category'])
            ->setSourceImc($bmiProfile['source'])
            ->setBesoinCalorique($bmiProfile['dailyCalories'])
            ->setCaloriesActivite($calorieProfile['totalCalories'])
            ->setSourceCalories($calorieProfile['source'])
            ->setIntensite($template['intensity'])
            ->setTypeProgramme($template['label'])
            ->setResume($this->buildSummary($programme, $bmiProfile, $calorieProfile, $template))
            ->setSeances($this->buildSessions($template))
        ;
    }

    /**
     * @return array{key: string, label: string, intensity: string, referenceActivity: string, referenceDuration: int, coaching: string, nutrition: string}
     */
    private function selectTemplate(ProgrammeSportif $programme, float $bmi): array
    {
        $stress = $programme->getNiveauStress() ?? 0;
        $sleepQuality = $programme->getQualiteSommeil() ?? 0;
        $sleepHours = $programme->getDureeSommeilHeures() ?? 0.0;
        $goal = $programme->getObjectif() ?? 'maintien';

        if ('recuperation' === $goal || $stress >= 8 || $sleepQuality <= 4 || $sleepHours < 6.0) {
            return [
                'key' => 'recovery',
                'label' => 'Recuperation active',
                'intensity' => 'Douce',
                'referenceActivity' => 'yoga',
                'referenceDuration' => 25,
                'coaching' => 'Priorite a la respiration, a la baisse de charge et aux mouvements sans impact.',
                'nutrition' => 'Hydratation et repas reguliers a privilegier.',
            ];
        }

        if ($bmi < 18.5) {
            return [
                'key' => 'strength',
                'label' => 'Renforcement progressif',
                'intensity' => 'Moderee',
                'referenceActivity' => 'strength training',
                'referenceDuration' => 35,
                'coaching' => 'Accent sur la posture, la technique et la progression calme.',
                'nutrition' => 'Ajoutez proteines et glucides autour des seances.',
            ];
        }

        if ('performance' === $goal && $stress <= 6 && $sleepQuality >= 6 && $sleepHours >= 6.5) {
            return [
                'key' => 'performance',
                'label' => 'Performance mixte',
                'intensity' => 'Soutenue',
                'referenceActivity' => 'running',
                'referenceDuration' => 45,
                'coaching' => 'Alterner vitesse, force et recuperation pour garder une bonne qualite de mouvement.',
                'nutrition' => 'Repartir les glucides autour des seances les plus exigeantes.',
            ];
        }

        if ('perte_poids' === $goal || $bmi >= 27) {
            return [
                'key' => 'fat_loss',
                'label' => 'Cardio progressif',
                'intensity' => 'Moderee',
                'referenceActivity' => 'walking',
                'referenceDuration' => 40,
                'coaching' => 'Volume regulier, faible impact et renforcement leger.',
                'nutrition' => 'Visez un leger deficit calorique sans sacrifier la recuperation.',
            ];
        }

        return [
            'key' => 'balanced',
            'label' => 'Equilibre global',
            'intensity' => 'Moderee',
            'referenceActivity' => $programme->getActiviteCible() ?? 'cycling',
            'referenceDuration' => 35,
            'coaching' => 'Mixer cardio, mobilite et force legere pour stabiliser la forme generale.',
            'nutrition' => 'Maintenir un apport stable en proteines, fibres et eau.',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSessions(array $template): array
    {
        return match ($template['key']) {
            'recovery' => [
                $this->session('Lundi', 'Respiration et mobilite', 20, 'Respiration diaphragmatique et mobilisation douce.'),
                $this->session('Mardi', 'Marche consciente', 25, 'Marche souple avec retour au calme.'),
                $this->session('Mercredi', 'Yoga doux', 25, 'Flux lent pour relancer la circulation.'),
                $this->session('Jeudi', 'Repos actif', 15, 'Etirements legers et posture.'),
                $this->session('Vendredi', 'Cardio leger', 25, 'Velo doux ou marche en aisance respiratoire.'),
                $this->session('Samedi', 'Mobilite globale', 20, 'Enchainement cou, epaules, bassin et chevilles.'),
                $this->session('Dimanche', 'Recuperation complete', 15, 'Routine courte de detente.'),
            ],
            'strength' => [
                $this->session('Lundi', 'Renforcement technique', 35, 'Mouvements globaux au poids du corps.'),
                $this->session('Mardi', 'Mobilite et gainage', 25, 'Stabilite du tronc et alignement.'),
                $this->session('Mercredi', 'Cardio doux', 25, 'Marche ou velo sans fatigue excessive.'),
                $this->session('Jeudi', 'Force globale', 35, 'Bas du corps, tirage et poussee.'),
                $this->session('Vendredi', 'Core training', 20, 'Gainage et respiration.'),
                $this->session('Samedi', 'Marche active', 30, 'Sortie exterieure reguliere.'),
                $this->session('Dimanche', 'Repos', 15, 'Mobilite fine et relachement musculaire.'),
            ],
            'performance' => [
                $this->session('Lundi', 'Intervalles cardio', 45, 'Blocs rapides puis retour au calme.'),
                $this->session('Mardi', 'Force fonctionnelle', 40, 'Travail jambes, poussee et tirage.'),
                $this->session('Mercredi', 'Mobilite active', 25, 'Souplesse dynamique.'),
                $this->session('Jeudi', 'Seance tempo', 40, 'Cardio soutenu a allure stable.'),
                $this->session('Vendredi', 'Renforcement explosif', 35, 'Puissance maitrisee et technique.'),
                $this->session('Samedi', 'Sortie longue', 50, 'Endurance continue en zone aerobie.'),
                $this->session('Dimanche', 'Recuperation active', 20, 'Marche et etirements.'),
            ],
            'fat_loss' => [
                $this->session('Lundi', 'Marche rapide', 40, 'Cadence confortable mais engagee.'),
                $this->session('Mardi', 'Circuit full body', 30, 'Squat, fente, tirage et gainage.'),
                $this->session('Mercredi', 'Mobilite', 20, 'Ouverture thoracique et hanches.'),
                $this->session('Jeudi', 'Cardio progressif', 35, 'Intervals doux 3 min actives / 2 min lentes.'),
                $this->session('Vendredi', 'Renforcement metabolique', 30, 'Exercices globaux en series courtes.'),
                $this->session('Samedi', 'Sortie exterieure', 45, 'Marche longue ou velo tranquille.'),
                $this->session('Dimanche', 'Stretching', 20, 'Souplesse et respiration.'),
            ],
            default => [
                $this->session('Lundi', 'Cardio modere', 35, 'Seance continue sur activite preferee.'),
                $this->session('Mardi', 'Renforcement', 30, 'Travail global au poids du corps.'),
                $this->session('Mercredi', 'Mobilite', 20, 'Souplesse active et respiration.'),
                $this->session('Jeudi', 'Cardio fractionne leger', 30, 'Relances courtes pour varier.'),
                $this->session('Vendredi', 'Gainage et posture', 20, 'Stabilite et dos.'),
                $this->session('Samedi', 'Sortie plaisir', 40, 'Activite exterieure en aisance.'),
                $this->session('Dimanche', 'Recuperation', 15, 'Routine courte de detente.'),
            ],
        };
    }

    private function buildSummary(
        ProgrammeSportif $programme,
        array $bmiProfile,
        array $calorieProfile,
        array $template,
    ): string {
        return sprintf(
            'Stress %d/10, sommeil %.1f h avec qualite %d/10. %s Programme recommande: %s a intensite %s. Seance de reference: %s pendant %d minutes pour environ %d kcal. %s %s',
            $programme->getNiveauStress() ?? 0,
            $programme->getDureeSommeilHeures() ?? 0.0,
            $programme->getQualiteSommeil() ?? 0,
            $bmiProfile['summary'],
            strtolower($template['label']),
            strtolower($template['intensity']),
            $calorieProfile['activity'],
            $calorieProfile['duration'],
            $calorieProfile['totalCalories'],
            $template['coaching'],
            $template['nutrition'],
        );
    }

    /**
     * @return array{jour: string, titre: string, duree: int, details: string}
     */
    private function session(string $day, string $title, int $duration, string $details): array
    {
        return [
            'jour' => $day,
            'titre' => $title,
            'duree' => $duration,
            'details' => $details,
        ];
    }
}
