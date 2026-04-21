<?php

namespace App\ProgrammeSportifBundle\Service;

use App\ProgrammeSportifBundle\Entity\ProgrammeSportif;

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
                'coaching' => 'Priorite a la baisse de charge, a la respiration et aux mouvements sans impact.',
                'nutrition' => 'Hydratation, repas reguliers et collation riche en magnesium a privilegier.',
            ];
        }

        if ($bmi < 18.5) {
            return [
                'key' => 'strength',
                'label' => 'Renforcement progressif',
                'intensity' => 'Moderee',
                'referenceActivity' => 'strength training',
                'referenceDuration' => 35,
                'coaching' => 'Accent sur la technique, la posture et la progression de charge.',
                'nutrition' => 'Ajouter un apport proteine/glucides autour des seances pour soutenir la progression.',
            ];
        }

        if ('performance' === $goal && $stress <= 6 && $sleepQuality >= 6 && $sleepHours >= 6.5) {
            return [
                'key' => 'performance',
                'label' => 'Performance mixte',
                'intensity' => 'Soutenue',
                'referenceActivity' => 'running',
                'referenceDuration' => 45,
                'coaching' => 'Alterner vitesse, force et recuperation pour conserver de la qualite de mouvement.',
                'nutrition' => 'Repartir les glucides autour des seances exigeantes et surveiller la recuperation.',
            ];
        }

        if ('perte_poids' === $goal || $bmi >= 27) {
            return [
                'key' => 'fat_loss',
                'label' => 'Cardio progressif',
                'intensity' => 'Moderee',
                'referenceActivity' => 'walking',
                'referenceDuration' => 40,
                'coaching' => 'Volume regulier, faible impact et ajout progressif de renforcement global.',
                'nutrition' => 'Viser un leger deficit calorique sans couper la recuperation ni les proteines.',
            ];
        }

        return [
            'key' => 'balanced',
            'label' => 'Equilibre global',
            'intensity' => 'Moderee',
            'referenceActivity' => $programme->getActiviteCible() ?? 'cycling',
            'referenceDuration' => 35,
            'coaching' => 'Mixer cardio, mobilite et force legere pour stabiliser la forme generale.',
            'nutrition' => 'Maintenir un apport stable en proteines, fibres et eau sur la semaine.',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSessions(array $template): array
    {
        return match ($template['key']) {
            'recovery' => [
                $this->session('Lundi', 'Respiration et mobilite', 20, 'Respiration diaphragmatique, ouverture des hanches et mobilisation du dos.'),
                $this->session('Mardi', 'Marche consciente', 25, 'Marche souple avec cadence reguliere et retour au calme en fin de seance.'),
                $this->session('Mercredi', 'Yoga doux', 25, 'Flux lent pour diminuer la tension et relancer la circulation.'),
                $this->session('Jeudi', 'Repos actif', 15, 'Etirements legers et travail de posture.'),
                $this->session('Vendredi', 'Cardio leger', 25, 'Velo doux ou marche incline en aisance respiratoire.'),
                $this->session('Samedi', 'Mobilite globale', 20, 'Enchainement cou, epaules, bassin et chevilles.'),
                $this->session('Dimanche', 'Recuperation complete', 15, 'Marche lente et routine de detente courte.'),
            ],
            'strength' => [
                $this->session('Lundi', 'Renforcement technique', 35, 'Mouvements polyarticulaires au poids du corps, tempo controle.'),
                $this->session('Mardi', 'Mobilite et gainage', 25, 'Stabilite du tronc et alignement postural.'),
                $this->session('Mercredi', 'Cardio doux', 25, 'Marche ou velo sans fatigue excessive.'),
                $this->session('Jeudi', 'Force globale', 35, 'Bas du corps, tirage et poussee avec volume modere.'),
                $this->session('Vendredi', 'Core training', 20, 'Gainage, respiration et travail de chaine posterieure.'),
                $this->session('Samedi', 'Marche active', 30, 'Sortie exterieure reguliere pour recuperer sans charge.'),
                $this->session('Dimanche', 'Repos', 15, 'Mobilite fine et relachement musculaire.'),
            ],
            'performance' => [
                $this->session('Lundi', 'Intervalles cardio', 45, 'Echauffement, blocs rapides puis retour au calme.'),
                $this->session('Mardi', 'Force fonctionnelle', 40, 'Travail jambes, poussee et tirage avec gainage.'),
                $this->session('Mercredi', 'Mobilite active', 25, 'Souplesse dynamique et relance articulaire.'),
                $this->session('Jeudi', 'Seance tempo', 40, 'Cardio soutenu a allure stable et controlee.'),
                $this->session('Vendredi', 'Renforcement explosif', 35, 'Plyometrie legere et travail de puissance maitrisee.'),
                $this->session('Samedi', 'Sortie longue', 50, 'Endurance continue en zone aerobie.'),
                $this->session('Dimanche', 'Recuperation active', 20, 'Marche, etirements et relachement.'),
            ],
            'fat_loss' => [
                $this->session('Lundi', 'Marche rapide', 40, 'Cadence confortable mais engagee, bras actifs.'),
                $this->session('Mardi', 'Circuit full body', 30, 'Squat, fente, tirage elastique et gainage en rotation.'),
                $this->session('Mercredi', 'Mobilite', 20, 'Ouverture thoracique et hanches pour garder de l amplitude.'),
                $this->session('Jeudi', 'Cardio progressif', 35, 'Intervals doux type 3 min actives / 2 min lentes.'),
                $this->session('Vendredi', 'Renforcement metabolique', 30, 'Exercices globaux en series courtes et propres.'),
                $this->session('Samedi', 'Sortie exterieure', 45, 'Marche longue ou velo tranquille.'),
                $this->session('Dimanche', 'Stretching', 20, 'Souplesse et respiration pour reduire la charge.'),
            ],
            default => [
                $this->session('Lundi', 'Cardio modere', 35, 'Seance continue sur activite preferee.'),
                $this->session('Mardi', 'Renforcement', 30, 'Travail global au poids du corps.'),
                $this->session('Mercredi', 'Mobilite', 20, 'Souplesse active et respiration.'),
                $this->session('Jeudi', 'Cardio fractionne leger', 30, 'Relances courtes pour varier l effort.'),
                $this->session('Vendredi', 'Gainage et posture', 20, 'Stabilite, dos et centre du corps.'),
                $this->session('Samedi', 'Sortie plaisir', 40, 'Activite exterieure en aisance respiratoire.'),
                $this->session('Dimanche', 'Recuperation', 15, 'Routine courte de detente.'),
            ],
        };
    }

    /**
     * @param array{bmi: float, category: string, dailyCalories: int, summary: string, source: string} $bmiProfile
     * @param array{activity: string, totalCalories: int, duration: int, source: string} $calorieProfile
     * @param array{key: string, label: string, intensity: string, referenceActivity: string, referenceDuration: int, coaching: string, nutrition: string} $template
     */
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
