<?php

namespace App\Service;

use App\Repository\RendezvouRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Recherche / filtres / tri / stats pour les rendez-vous.
 */
class RendezVousMetierService
{
    public function __construct(
        private readonly RendezvouRepository $rendezvouRepository
    ) {
    }

    /**
     * @return array{
     *   rendezvous: list,
     *   stats: array,
     *   filters: array{q: string, statut: string, date_from: string, date_to: string, sort: string, dir: string}
     * }
     */
    public function buildListeData(Request $request): array
    {
        $q = trim((string) $request->query->get('q', ''));
        $statut = trim((string) $request->query->get('statut', ''));
        $dateFromStr = trim((string) $request->query->get('date_from', ''));
        $dateToStr = trim((string) $request->query->get('date_to', ''));
        $sort = trim((string) $request->query->get('sort', 'date')) ?: 'date';
        $dir = trim((string) $request->query->get('dir', 'desc')) ?: 'desc';

        $dateFrom = $this->parseDate($dateFromStr);
        $dateTo = $this->parseDate($dateToStr);

        return [
            'rendezvous' => $this->rendezvouRepository->findFiltered(
                $q !== '' ? $q : null,
                $statut !== '' ? $statut : null,
                $dateFrom,
                $dateTo,
                $sort,
                $dir
            ),
            'stats' => $this->rendezvouRepository->getStatistics(),
            'filters' => [
                'q' => $q,
                'statut' => $statut,
                'date_from' => $dateFromStr,
                'date_to' => $dateToStr,
                'sort' => $sort,
                'dir' => $dir,
            ],
        ];
    }

    private function parseDate(string $s): ?\DateTimeInterface
    {
        if ($s === '') {
            return null;
        }
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $s);

        return $d instanceof \DateTimeImmutable ? $d : null;
    }
}
