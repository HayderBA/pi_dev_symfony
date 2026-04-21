<?php

namespace App\Service;

class BrevoSmsService
{
    private $apiKey;
    
    public function __construct()
    {
        $this->apiKey = $_ENV['BREVO_API_KEY'] ?? null;
    }
    
    public function sendSms(string $phoneNumber, string $message): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'Clé API Brevo manquante'];
        }
        
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        $data = [
            'sender' => $_ENV['BREVO_SMS_FROM'] ?? 'GROWMIND',
            'recipient' => $phoneNumber,
            'content' => substr($message, 0, 160),
            'type' => 'transactional'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/transactionalSMS/sms');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'api-key: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201 || $httpCode === 200) {
            return ['success' => true, 'response' => json_decode($response, true)];
        }
        
        return ['success' => false, 'error' => "HTTP $httpCode: $response"];
    }
    
    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 8) {
            return '+216' . $phone;
        }
        if (strlen($phone) === 9 && $phone[0] === '0') {
            return '+216' . substr($phone, 1);
        }
        return $phone;
    }
}