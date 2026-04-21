<?php

namespace App\Service;

class TelegramService
{
    private $botToken;
    private $chatId;
    
    public function __construct()
    {
        $this->botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
        $this->chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? null;
    }
    
    public function sendMessage(string $message): bool
    {
        if (!$this->botToken || !$this->chatId) {
            return false;
        }
        
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        $data = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    public function sendUrgence(string $patientNom, float $distance, string $lien): bool
    {
        $message = "🚨 <b>URGENCE GrowMind</b> 🚨\n\n";
        $message .= "Patient: <b>{$patientNom}</b>\n";
        $message .= "Distance: <b>{$distance} km</b>\n";
        $message .= "Localisation: <a href='{$lien}'>Google Maps</a>\n\n";
        $message .= "Veuillez contacter le patient immédiatement.";
        
        return $this->sendMessage($message);
    }
}