<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class FirebaseNotificationService
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function envoyerNotification(string $token, string $titre, string $corps, array $donnees = []): bool
    {
        if ($token === '') {
            return false;
        }

        $this->logger->info('Notification FCM simulée', [
            'token' => $token,
            'titre' => $titre,
            'corps' => $corps,
            'donnees' => $donnees,
        ]);

        return true;
    }

    public function envoyerUrgence(string $token, string $patientNom, float $distance): bool
    {
        return $this->envoyerNotification(
            $token,
            'URGENCE GrowMind',
            sprintf('Patient %s a %.2f km - Besoin d aide', $patientNom, $distance),
            ['type' => 'urgence', 'distance' => (string) $distance]
        );
    }
}
