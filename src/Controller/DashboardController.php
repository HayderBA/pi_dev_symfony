<?php

namespace App\Controller;

use App\Entity\SanteBienEtre;
use App\Entity\SleepTracking;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/sante-bien-etre/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
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
            ->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.santeBienEtres', 's')
            ->select('u.id AS id, u.nom AS nom, u.email AS email, COUNT(s.id) AS recordCount')
            ->groupBy('u.id, u.nom, u.email')
            ->orderBy('recordCount', 'DESC')
            ->addOrderBy('u.nom', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $totalUsers = (int) $entityManager
            ->getRepository(Utilisateur::class)
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

        return $this->render('dashboard.html.twig', [
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

            if (! isset($groupedData[$dateLabel])) {
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

            if (! isset($groupedData[$dateLabel])) {
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
     * @param array<int, array{id: int, nom: string, email: string, recordCount: numeric-string}> $records
     *
     * @return array{labels: string[], values: int[]}
     */
    private function buildRecordsPerUserData(array $records): array
    {
        $labels = [];
        $values = [];

        foreach ($records as $record) {
            $labels[] = $record['nom'] ?: ($record['email'] ?: 'Utilisateur #'.$record['id']);
            $values[] = (int) $record['recordCount'];
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
