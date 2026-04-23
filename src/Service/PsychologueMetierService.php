<?php

namespace App\Service;

use App\Repository\CabinetRepository;
use App\Repository\PsychologueRepository;
use Symfony\Component\HttpFoundation\Request;

class PsychologueMetierService
{
    public function __construct(
        private readonly PsychologueRepository $psychologueRepository,
        private readonly CabinetRepository $cabinetRepository,
    ) {
    }

    public function buildListeData(Request $request): array
    {
        $q = trim((string) $request->query->get('q', ''));
        $cabinetId = $request->query->getInt('cabinet', 0);

        return [
            'psychologues' => $this->psychologueRepository->findFiltered($q ?: null, $cabinetId ?: null),
            'stats' => $this->psychologueRepository->getStatistics(),
            'cabinets' => $this->cabinetRepository->findBy([], ['nomcabinet' => 'ASC']),
            'filters' => ['q' => $q, 'cabinet' => $cabinetId],
        ];
    }
}
