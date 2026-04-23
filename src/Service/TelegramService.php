<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramService
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function sendMessage(string $message, ?string $chatId = null): bool
    {
        $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
        $chatId = $chatId ?: ($_ENV['TELEGRAM_CHAT_ID'] ?? null);

        if (!$botToken || !$chatId) {
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', sprintf('https://api.telegram.org/bot%s/sendMessage', $botToken), [
                'body' => [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ],
            ]);

            return $response->getStatusCode() < 300;
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendUrgence(string $patientNom, float $distance, string $lien, ?string $chatId = null): bool
    {
        $message = "URGENTE GrowMind\n\n";
        $message .= "Patient: <b>{$patientNom}</b>\n";
        $message .= "Distance: <b>" . number_format($distance, 2) . " km</b>\n";
        $message .= "Localisation: <a href='{$lien}'>Google Maps</a>\n\n";
        $message .= "Veuillez contacter le patient immediatement.";

        return $this->sendMessage($message, $chatId);
    }
}
