<?php

namespace App\Service;

use App\Repository\CabinetRepository;
use Symfony\Component\HttpFoundation\Request;

class CabinetMetierService
{
    public function __construct(private readonly CabinetRepository $cabinetRepository)
    {
    }

    public function buildListeData(Request $request): array
    {
        $q = trim((string) $request->query->get('q', ''));
        $status = trim((string) $request->query->get('status', ''));
        $ville = trim((string) $request->query->get('ville', ''));
        $sort = trim((string) $request->query->get('sort', 'nom')) ?: 'nom';
        $dir = trim((string) $request->query->get('dir', 'asc')) ?: 'asc';

        return [
            'cabinets' => $this->cabinetRepository->findFiltered($q ?: null, $status ?: null, $ville ?: null, $sort, $dir),
            'stats' => $this->cabinetRepository->getStatistics(),
            'villes' => $this->cabinetRepository->findDistinctVilles(),
            'filters' => ['q' => $q, 'status' => $status, 'ville' => $ville, 'sort' => $sort, 'dir' => $dir],
        ];
    }
}
