<?php

namespace App\Service;

use App\Repository\RendezvousRepository;
use Symfony\Component\HttpFoundation\Request;

class RendezVousMetierService
{
    public function __construct(private readonly RendezvousRepository $rendezvousRepository)
    {
    }

    public function buildListeData(Request $request): array
    {
        $q = trim((string) $request->query->get('q', ''));
        $statut = trim((string) $request->query->get('statut', ''));
        $dateFromStr = trim((string) $request->query->get('date_from', ''));
        $dateToStr = trim((string) $request->query->get('date_to', ''));
        $sort = trim((string) $request->query->get('sort', 'date')) ?: 'date';
        $dir = trim((string) $request->query->get('dir', 'desc')) ?: 'desc';

        $dateFrom = $dateFromStr !== '' ? \DateTimeImmutable::createFromFormat('Y-m-d', $dateFromStr) ?: null : null;
        $dateTo = $dateToStr !== '' ? \DateTimeImmutable::createFromFormat('Y-m-d', $dateToStr) ?: null : null;

        return [
            'rendezvous' => $this->rendezvousRepository->findFiltered($q ?: null, $statut ?: null, $dateFrom, $dateTo, $sort, $dir),
            'stats' => $this->rendezvousRepository->getStatistics(),
            'filters' => ['q' => $q, 'statut' => $statut, 'date_from' => $dateFromStr, 'date_to' => $dateToStr, 'sort' => $sort, 'dir' => $dir],
        ];
    }
}
