<?php

namespace App\Tests\Service;

use App\Service\IMCApiService;
use PHPUnit\Framework\TestCase;

final class IMCApiServiceTest extends TestCase
{
    public function testItCalculatesNormalImc(): void
    {
        $service = new IMCApiService();

        $result = $service->calculate(70.0, 1.75);

        self::assertSame(22.9, $result['imc']);
        self::assertSame('normal', $result['categorie']);
    }

    public function testItCalculatesSurpoidsImc(): void
    {
        $service = new IMCApiService();

        $result = $service->calculate(90.0, 1.75);

        self::assertSame('surpoids', $result['categorie']);
    }
}
