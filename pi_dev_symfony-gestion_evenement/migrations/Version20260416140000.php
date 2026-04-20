<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionAddEvenementColumns extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des colonnes is_dynamic_price_active et seat_categories à evenement';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement ADD COLUMN is_dynamic_price_active TINYINT(1) DEFAULT 0');
        $this->addSql('ALTER TABLE evenement ADD COLUMN seat_categories JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement DROP COLUMN is_dynamic_price_active');
        $this->addSql('ALTER TABLE evenement DROP COLUMN seat_categories');
    }
}
