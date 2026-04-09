<?php

namespace App\Service;

use App\Repository\CabinetRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Regroupe recherche / filtres / tri / stats pour la liste cabinets (couche métier simple).
 */
class CabinetMetierService
{
    public function __construct(
        private readonly CabinetRepository $cabinetRepository
    ) {
    }

    /**
     * @return array{
     *   cabinets: list,
     *   stats: array{total: int, by_status: array<string, int>},
     *   villes: list<string>,
     *   filters: array{q: string, status: string, ville: string, sort: string, dir: string}
     * }
     */
    public function buildListeData(Request $request): array
    {
        $q = trim((string) $request->query->get('q', ''));
        $status = trim((string) $request->query->get('status', ''));
        $ville = trim((string) $request->query->get('ville', ''));
        $sort = trim((string) $request->query->get('sort', 'nom')) ?: 'nom';
        $dir = trim((string) $request->query->get('dir', 'asc')) ?: 'asc';

        return [
            'cabinets' => $this->cabinetRepository->findFiltered(
                $q !== '' ? $q : null,
                $status !== '' ? $status : null,
                $ville !== '' ? $ville : null,
                $sort,
                $dir
            ),
            'stats' => $this->cabinetRepository->getStatistics(),
            'villes' => $this->cabinetRepository->findDistinctVilles(),
            'filters' => [
                'q' => $q,
                'status' => $status,
                'ville' => $ville,
                'sort' => $sort,
                'dir' => $dir,
            ],
        ];
    }
}
