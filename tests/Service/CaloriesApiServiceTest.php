<?php

namespace App\Tests\Service;

use App\Service\CaloriesApiService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CaloriesApiServiceTest extends TestCase
{
    public function testItReturnsCaloriesFromApiPayload(): void
    {
        $capturedRequest = null;

        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedRequest): MockResponse {
            $capturedRequest = [
                'method' => $method,
                'url' => $url,
                'options' => $options,
            ];

            return new MockResponse(json_encode([
                ['name' => 'apple', 'calories' => 95.2],
                ['name' => 'banana', 'calories' => 105.0],
            ], JSON_THROW_ON_ERROR));
        });

        $service = new CaloriesApiService(
            $httpClient,
            new NullLogger(),
            'https://api.api-ninjas.com/v1/nutrition',
            'test-key',
            2000
        );

        $result = $service->fetchNutritionData('1 apple and 1 banana');

        self::assertSame(200, $result['calories']);
        self::assertSame('api', $result['source']);
        self::assertCount(2, $result['items']);
        self::assertNull($result['error']);
        self::assertNotNull($capturedRequest);
        self::assertSame('GET', $capturedRequest['method']);
        self::assertStringStartsWith('https://api.api-ninjas.com/v1/nutrition', $capturedRequest['url']);
        self::assertStringContainsString('query=1%20apple%20and%201%20banana', $capturedRequest['url']);
        $normalizedHeaders = $capturedRequest['options']['normalized_headers'] ?? [];
        $flatHeaders = [];
        foreach ($normalizedHeaders as $headerValues) {
            foreach ((array) $headerValues as $headerValue) {
                $flatHeaders[] = strtolower((string) $headerValue);
            }
        }
        self::assertContains('x-api-key: test-key', $flatHeaders);
    }

    public function testItEstimatesCaloriesFromMacrosWhenCaloriesFieldIsNotNumeric(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'name' => 'apple',
                    'calories' => 'Only available for premium subscribers.',
                    'fat_total_g' => 0.3,
                    'carbohydrates_total_g' => 25.6,
                    'protein_g' => 'Only available for premium subscribers.',
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $service = new CaloriesApiService(
            $httpClient,
            new NullLogger(),
            'https://api.api-ninjas.com/v1/nutrition',
            'test-key',
            2000
        );

        $result = $service->fetchNutritionData('1 apple');

        // Atwater: 4 * 0 + 4 * 25.6 + 9 * 0.3 = 105.1 -> 105 kcal arrondi
        self::assertSame(105, $result['calories']);
        self::assertSame('api', $result['source']);
        self::assertNull($result['error']);
        self::assertCount(1, $result['items']);
        self::assertSame(105.1, $result['items'][0]['calories']);
    }

    public function testItReturnsDefaultCaloriesWhenApiIsNotConfigured(): void
    {
        $service = new CaloriesApiService(
            new MockHttpClient(),
            new NullLogger(),
            '',
            '',
            2000
        );

        $result = $service->fetchNutritionData('1 apple');

        self::assertSame(2000, $result['calories']);
        self::assertSame('fallback', $result['source']);
        self::assertSame('Configuration API absente. Renseigne API_NINJAS_KEY dans .env.local.', $result['error']);
    }

    public function testItTreatsPlaceholderValuesAsMissingConfiguration(): void
    {
        $service = new CaloriesApiService(
            new MockHttpClient(),
            new NullLogger(),
            'https://api.api-ninjas.com/v1/nutrition',
            'PASTE_YOUR_API_NINJAS_KEY_HERE',
            2000
        );

        $result = $service->fetchNutritionData('1 apple');

        self::assertSame(2000, $result['calories']);
        self::assertSame('fallback', $result['source']);
        self::assertSame('Configuration API absente. Renseigne API_NINJAS_KEY dans .env.local.', $result['error']);
    }

    public function testItReturnsFallbackWithApiErrorDetails(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(
                json_encode(['message' => 'Unauthorized'], JSON_THROW_ON_ERROR),
                ['http_code' => 401]
            ),
        ]);

        $service = new CaloriesApiService(
            $httpClient,
            new NullLogger(),
            'https://api.api-ninjas.com/v1/nutrition',
            'real-key',
            2000
        );

        $result = $service->fetchNutritionData('1 apple');

        self::assertSame(2000, $result['calories']);
        self::assertSame('fallback', $result['source']);
        self::assertSame('Erreur API Ninjas (401): Unauthorized. Verifie API_NINJAS_KEY.', $result['error']);
    }

    public function testItReturnsFallbackWhenTransportFails(): void
    {
        $httpClient = new MockHttpClient(static function (): never {
            throw new TransportException('TLS handshake failed');
        });

        $service = new CaloriesApiService(
            $httpClient,
            new NullLogger(),
            'https://api.api-ninjas.com/v1/nutrition',
            'real-key',
            2000
        );

        $result = $service->fetchNutritionData('1 apple');

        self::assertSame(2000, $result['calories']);
        self::assertSame('fallback', $result['source']);
        self::assertSame(
            'Connexion a API Ninjas impossible. Verifie la connectivite reseau.',
            $result['error']
        );
    }
}
