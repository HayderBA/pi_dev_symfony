<?php

namespace App\Service;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    private $messaging;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(__DIR__ . '/../../config/firebase_credentials.json');
        $this->messaging = $factory->createMessaging();
    }

    public function envoyerNotification(string $token, string $titre, string $corps, array $donnees = []): bool
    {
        try {
            $notification = Notification::create($titre, $corps);
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($donnees);
            
            $this->messaging->send($message);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function envoyerUrgence(string $token, string $patientNom, float $distance): bool
    {
        return $this->envoyerNotification(
            $token,
            'URGENCE GrowMind',
            "Patient {$patientNom} à {$distance} km - Besoin d'aide",
            ['type' => 'urgence', 'distance' => (string)$distance]
        );
    }
}