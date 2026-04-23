<?php

namespace App\Service;

use App\Entity\Reservation;

class QrCodeService
{
    public function generate(string $data, ?Reservation $reservation = null): string
    {
        $payload = $this->buildPayload($data, $reservation);
        $ticketCode = strtoupper(substr(sha1($payload), 0, 10));
        $eventTitle = $reservation?->getEvenement()?->getTitre() ?? 'GrowMind Event';
        $seat = $reservation?->getSeatNumber() ?? 'Auto';

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="320" height="320" viewBox="0 0 320 320" role="img" aria-label="Ticket QR"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%%" stop-color="#6BBF59"/><stop offset="100%%" stop-color="#1E5BA8"/></linearGradient></defs><rect width="320" height="320" rx="28" fill="#ffffff"/><rect x="18" y="18" width="284" height="284" rx="24" fill="url(#g)" opacity="0.10"/><rect x="36" y="36" width="248" height="248" rx="22" fill="#ffffff" stroke="#d9e7f5" stroke-width="2"/><text x="50%%" y="88" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="#183153">GrowMind</text><text x="50%%" y="122" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="#44607c">Billet evenement</text><text x="50%%" y="166" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" font-weight="700" fill="#1E5BA8">%s</text><text x="50%%" y="194" text-anchor="middle" font-family="Arial, sans-serif" font-size="13" fill="#5d6d7c">Place %s</text><text x="50%%" y="236" text-anchor="middle" font-family="Courier New, monospace" font-size="24" font-weight="700" fill="#183153">%s</text><text x="50%%" y="264" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" fill="#7a8b9d">Valide jusqu a la fin de la date de l evenement</text></svg>',
            htmlspecialchars($this->truncate($eventTitle, 28), ENT_QUOTES),
            htmlspecialchars($seat, ENT_QUOTES),
            htmlspecialchars($ticketCode, ENT_QUOTES)
        );

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    public function isValid(?Reservation $reservation): bool
    {
        if (!$reservation || !$reservation->getEvenement() || !$reservation->getEvenement()->getDate()) {
            return false;
        }

        $expiration = \DateTimeImmutable::createFromInterface($reservation->getEvenement()->getDate())->setTime(23, 59, 59);

        return new \DateTimeImmutable() <= $expiration;
    }

    private function buildPayload(string $data, ?Reservation $reservation): string
    {
        $decoded = json_decode($data, true);
        $payload = is_array($decoded) ? $decoded : ['content' => $data];

        if ($reservation && $reservation->getEvenement()) {
            $eventDate = \DateTimeImmutable::createFromInterface($reservation->getEvenement()->getDate());
            $payload['expires_at'] = $eventDate->setTime(23, 59, 59)->format(\DateTimeInterface::ATOM);
            $payload['event_title'] = $reservation->getEvenement()->getTitre();
            $payload['seat_number'] = $reservation->getSeatNumber();
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: $data;
    }

    private function truncate(string $value, int $length): string
    {
        return mb_strlen($value) > $length ? mb_substr($value, 0, $length - 1) . '…' : $value;
    }
}
