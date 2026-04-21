<?php

namespace App\Tests\ProgrammeSportifBundle\Service;

use App\Entity\Utilisateur;
use App\ProgrammeSportifBundle\Entity\ProgrammeSportif;
use App\ProgrammeSportifBundle\Service\BmiApiClient;
use App\ProgrammeSportifBundle\Service\CalorieApiClient;
use App\ProgrammeSportifBundle\Service\ProgrammeSportifGenerator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;

final class ProgrammeSportifGeneratorTest extends TestCase
{
    public function testItBuildsARecoveryProgramWhenStressIsHigh(): void
    {
        $generator = $this->createGenerator();
        $programme = $this->createProgramme()
            ->setNiveauStress(9)
            ->setQualiteSommeil(3)
            ->setDureeSommeilHeures(5.2)
            ->setObjectif('maintien');

        $generator->generate($programme);

        self::assertSame('Recuperation active', $programme->getTypeProgramme());
        self::assertSame('Douce', $programme->getIntensite());
        self::assertSame('Calcul local', $programme->getSourceImc());
        self::assertSame('Calcul local', $programme->getSourceCalories());
        self::assertNotEmpty($programme->getSeances());
    }

    public function testItBuildsAPerformanceProgramWhenProfileIsStable(): void
    {
        $generator = $this->createGenerator();
        $programme = $this->createProgramme()
            ->setNiveauStress(4)
            ->setQualiteSommeil(8)
            ->setDureeSommeilHeures(7.5)
            ->setObjectif('performance');

        $generator->generate($programme);

        self::assertSame('Performance mixte', $programme->getTypeProgramme());
        self::assertSame('Soutenue', $programme->getIntensite());
        self::assertGreaterThan(0, $programme->getBesoinCalorique());
        self::assertGreaterThan(0, $programme->getCaloriesActivite());
    }

    private function createGenerator(): ProgrammeSportifGenerator
    {
        $httpClient = new MockHttpClient();
        $logger = new NullLogger();

        return new ProgrammeSportifGenerator(
            new BmiApiClient($httpClient, $logger, '', ''),
            new CalorieApiClient($httpClient, $logger, '', ''),
        );
    }

    private function createProgramme(): ProgrammeSportif
    {
        $user = (new Utilisateur())
            ->setNom('Alice')
            ->setEmail('alice@example.com');

        return (new ProgrammeSportif())
            ->setUser($user)
            ->setAge(30)
            ->setGenre('female')
            ->setTailleCm(168.0)
            ->setPoidsKg(64.0)
            ->setNiveauStress(5)
            ->setQualiteSommeil(7)
            ->setDureeSommeilHeures(7.0)
            ->setNiveauActivite('moderate')
            ->setObjectif('maintien')
            ->setActiviteCible('cycling');
    }
}
