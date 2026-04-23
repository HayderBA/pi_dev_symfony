<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class NtfyService
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    public function send(string $topic, string $title, string $message): array
    {
        try {
            $response = $this->client->request('POST', 'https://ntfy.sh/' . rawurlencode($topic), [
                'headers' => [
                    'Title' => $title,
                    'Priority' => 'high',
                    'Tags' => 'bell',
                ],
                'body' => $message,
            ]);

            return [
                'success' => 200 === $response->getStatusCode(),
                'topic' => $topic,
            ];
        } catch (\Throwable) {
            return [
                'success' => false,
                'topic' => $topic,
            ];
        }
    }
}
