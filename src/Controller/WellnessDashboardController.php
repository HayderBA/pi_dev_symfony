<?php

namespace App\Controller;

use App\Entity\Conseil;
use App\Entity\Humeur;
use App\Entity\ProgrammeSportif;
use App\Entity\SanteBienEtre;
use App\Entity\SleepTracking;
use App\Entity\User;
use App\Entity\WellnessTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WellnessDashboardController extends AbstractController
{
    #[Route('/bien-etre', name: 'app_wellness_dashboard', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $records = $entityManager->getRepository(SanteBienEtre::class)->findBy([], ['dateSuivi' => 'DESC'], 5);
        $humeurs = $entityManager->getRepository(Humeur::class)->findBy([], ['dateCreation' => 'DESC'], 5);
        $sleepEntries = $entityManager->getRepository(SleepTracking::class)->findBy([], ['dateSommeil' => 'DESC'], 5);
        $programmes = $entityManager->getRepository(ProgrammeSportif::class)->findBy([], ['createdAt' => 'DESC'], 3);

        return $this->render('wellness/dashboard.html.twig', [
            'stats' => [
                'suivis' => $entityManager->getRepository(SanteBienEtre::class)->count([]),
                'humeurs' => $entityManager->getRepository(Humeur::class)->count([]),
                'sommeil' => $entityManager->getRepository(SleepTracking::class)->count([]),
                'tests' => $entityManager->getRepository(WellnessTest::class)->count([]),
                'conseils' => $entityManager->getRepository(Conseil::class)->count([]),
                'programmes' => $entityManager->getRepository(ProgrammeSportif::class)->count([]),
            ],
            'records' => $records,
            'humeurs' => $humeurs,
            'sleep_entries' => $sleepEntries,
            'programmes' => $programmes,
        ]);
    }

    #[Route('/back/bien-etre', name: 'app_wellness_admin', methods: ['GET'])]
    public function admin(EntityManagerInterface $entityManager): Response
    {
        $records = $entityManager->getRepository(SanteBienEtre::class)->findBy([], ['dateCreation' => 'DESC'], 6);
        $tests = $entityManager->getRepository(WellnessTest::class)->findBy([], ['dateTest' => 'DESC'], 6);
        $santeBienEtreRecords = $entityManager
            ->getRepository(SanteBienEtre::class)
            ->createQueryBuilder('s')
            ->orderBy('s.dateSuivi', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();

        $sleepTrackingRecords = $entityManager
            ->getRepository(SleepTracking::class)
            ->createQueryBuilder('st')
            ->orderBy('st.dateSommeil', 'ASC')
            ->addOrderBy('st.id', 'ASC')
            ->getQuery()
            ->getResult();

        $healthRecordCountsPerUser = $entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.santeBienEtres', 's')
            ->select('u.id AS id, u.name AS name, u.secondName AS secondName, u.email AS email, COUNT(s.id) AS recordCount')
            ->groupBy('u.id, u.name, u.secondName, u.email')
            ->orderBy('recordCount', 'DESC')
            ->addOrderBy('u.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $totalUsers = (int) $entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalSanteBienEtreRecords = (int) $entityManager
            ->getRepository(SanteBienEtre::class)
            ->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $averageStressLevel = $entityManager
            ->getRepository(SanteBienEtre::class)
            ->createQueryBuilder('s')
            ->select('AVG(s.niveauStress)')
            ->getQuery()
            ->getSingleScalarResult();

        $averageSleepDuration = $entityManager
            ->getRepository(SleepTracking::class)
            ->createQueryBuilder('st')
            ->select('AVG(st.dureeMinutes)')
            ->getQuery()
            ->getSingleScalarResult();

        $stressEvolution = $this->buildStressEvolutionData($santeBienEtreRecords);
        $sleepTrackingEvolution = $this->buildSleepTrackingEvolutionData($sleepTrackingRecords);
        $recordsPerUser = $this->buildRecordsPerUserData($healthRecordCountsPerUser);

        return $this->render('wellness/admin.html.twig', [
            'stats' => [
                'suivis' => $entityManager->getRepository(SanteBienEtre::class)->count([]),
                'humeurs' => $entityManager->getRepository(Humeur::class)->count([]),
                'sommeil' => $entityManager->getRepository(SleepTracking::class)->count([]),
                'conseils' => $entityManager->getRepository(Conseil::class)->count([]),
                'tests' => $entityManager->getRepository(WellnessTest::class)->count([]),
                'programmes' => $entityManager->getRepository(ProgrammeSportif::class)->count([]),
            ],
            'records' => $records,
            'tests' => $tests,
            'total_users' => $totalUsers,
            'total_sante_bien_etre_records' => $totalSanteBienEtreRecords,
            'average_stress_level' => null !== $averageStressLevel ? round((float) $averageStressLevel, 2) : null,
            'average_sleep_duration' => null !== $averageSleepDuration ? round((float) $averageSleepDuration, 2) : null,
            'stress_evolution_labels' => $stressEvolution['labels'],
            'stress_evolution_values' => $stressEvolution['values'],
            'sleep_tracking_labels' => $sleepTrackingEvolution['labels'],
            'sleep_duration_values' => $sleepTrackingEvolution['durationValues'],
            'sleep_quality_values' => $sleepTrackingEvolution['qualityValues'],
            'records_per_user_labels' => $recordsPerUser['labels'],
            'records_per_user_values' => $recordsPerUser['values'],
        ]);
    }

    #[Route('/bien-etre/stats', name: 'app_wellness_stats', methods: ['GET'])]
    public function stats(EntityManagerInterface $entityManager): Response
    {
        $records = $entityManager->getRepository(SanteBienEtre::class)->findBy([], ['dateSuivi' => 'ASC']);

        $dates = [];
        $stressLevels = [];
        $sleepQualities = [];

        foreach ($records as $record) {
            $dates[] = $record->getDateSuivi()?->format('Y-m-d');
            $stressLevels[] = $record->getNiveauStress();
            $sleepQualities[] = $record->getQualiteSommeil();
        }

        $statsRows = array_map(static function (SanteBienEtre $record): array {
            return [
                'date' => $record->getDateSuivi()?->format('d/m/Y'),
                'stress_level' => $record->getNiveauStress(),
                'sleep_quality' => $record->getQualiteSommeil(),
            ];
        }, $records);

        return $this->render('wellness/stats.html.twig', [
            'dates' => $dates,
            'stress_levels' => $stressLevels,
            'sleep_qualities' => $sleepQualities,
            'stats_rows' => $statsRows,
        ]);
    }

    /**
     * @param SanteBienEtre[] $records
     *
     * @return array{labels: string[], values: float[]}
     */
    private function buildStressEvolutionData(array $records): array
    {
        $groupedData = [];

        foreach ($records as $record) {
            $dateLabel = $record->getDateSuivi()?->format('Y-m-d');

            if (null === $dateLabel) {
                continue;
            }

            if (!isset($groupedData[$dateLabel])) {
                $groupedData[$dateLabel] = [
                    'sum' => 0,
                    'count' => 0,
                ];
            }

            $groupedData[$dateLabel]['sum'] += (int) $record->getNiveauStress();
            ++$groupedData[$dateLabel]['count'];
        }

        $labels = [];
        $values = [];

        foreach ($groupedData as $dateLabel => $group) {
            $labels[] = $dateLabel;
            $values[] = round($group['sum'] / max($group['count'], 1), 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @param SleepTracking[] $records
     *
     * @return array{labels: string[], durationValues: float[], qualityValues: float[]}
     */
    private function buildSleepTrackingEvolutionData(array $records): array
    {
        $groupedData = [];

        foreach ($records as $record) {
            $dateLabel = $record->getDateSommeil()?->format('Y-m-d');

            if (null === $dateLabel) {
                continue;
            }

            if (!isset($groupedData[$dateLabel])) {
                $groupedData[$dateLabel] = [
                    'durationSum' => 0,
                    'qualitySum' => 0,
                    'count' => 0,
                ];
            }

            $groupedData[$dateLabel]['durationSum'] += (int) $record->getDureeMinutes();
            $groupedData[$dateLabel]['qualitySum'] += (int) $record->getQualiteSommeil();
            ++$groupedData[$dateLabel]['count'];
        }

        $labels = [];
        $durationValues = [];
        $qualityValues = [];

        foreach ($groupedData as $dateLabel => $group) {
            $labels[] = $dateLabel;
            $durationValues[] = round($group['durationSum'] / max($group['count'], 1), 2);
            $qualityValues[] = round($group['qualitySum'] / max($group['count'], 1), 2);
        }

        return [
            'labels' => $labels,
            'durationValues' => $durationValues,
            'qualityValues' => $qualityValues,
        ];
    }

    /**
     * @param array<int, array{id: int, name: string, secondName: string, email: string, recordCount: numeric-string}> $records
     *
     * @return array{labels: string[], values: int[]}
     */
    private function buildRecordsPerUserData(array $records): array
    {
        $labels = [];
        $values = [];

        foreach ($records as $record) {
            $fullName = trim(($record['name'] ?? '') . ' ' . ($record['secondName'] ?? ''));
            $labels[] = $fullName !== '' ? $fullName : ($record['email'] ?: 'Utilisateur #' . $record['id']);
            $values[] = (int) $record['recordCount'];
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
