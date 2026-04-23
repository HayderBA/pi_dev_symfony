<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrevoSmsService
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function sendSms(string $phoneNumber, string $message): array
    {
        $apiKey = $_ENV['BREVO_API_KEY'] ?? null;
        if (!$apiKey) {
            return ['success' => false, 'error' => 'Cle API Brevo manquante'];
        }

        try {
            $response = $this->httpClient->request('POST', 'https://api.brevo.com/v3/transactionalSMS/sms', [
                'headers' => [
                    'Accept' => 'application/json',
                    'api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'sender' => $_ENV['BREVO_SMS_FROM'] ?? 'GROWMIND',
                    'recipient' => $this->formatPhoneNumber($phoneNumber),
                    'content' => mb_substr($message, 0, 160),
                    'type' => 'transactional',
                ],
            ]);

            return [
                'success' => $response->getStatusCode() < 300,
                'response' => $response->toArray(false),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if (strlen($phone) === 8) {
            return '+216' . $phone;
        }

        if (strlen($phone) === 9 && $phone[0] === '0') {
            return '+216' . substr($phone, 1);
        }

        return $phone;
    }
}
