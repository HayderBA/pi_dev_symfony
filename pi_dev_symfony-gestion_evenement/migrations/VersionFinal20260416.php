<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionFinal20260416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronisation complète - ajouter colonnes reservation et ajustements';
    }

    public function up(Schema $schema): void
    {
        // Ajouter les colonnes manquantes à reservation
        $this->addSql('ALTER TABLE reservation ADD qr_code LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD seat_number VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP COLUMN qr_code');
        $this->addSql('ALTER TABLE reservation DROP COLUMN seat_number');
    }
}
