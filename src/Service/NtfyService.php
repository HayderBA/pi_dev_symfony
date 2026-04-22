<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class NtfyService
{
    private $httpClient;
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    public function send(string $topic, string $title, string $message): array
    {
        try {
            $response = $this->httpClient->request('POST', 'https://ntfy.sh/' . $topic, [
                'headers' => [
                    'Title' => $title,
                    'Priority' => 'high',
                    'Tags' => 'bell',
                ],
                'body' => $message
            ]);
            
            return [
                'success' => $response->getStatusCode() === 200,
                'topic' => $topic
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}