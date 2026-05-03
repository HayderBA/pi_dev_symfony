<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260430125024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make content column nullable to allow resources without manual content';
    }

    public function up(Schema $schema): void
    {
        // Make content column nullable
        $this->addSql('ALTER TABLE ressource MODIFY content TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Make content column NOT NULL again
        $this->addSql('ALTER TABLE ressource MODIFY content TEXT NOT NULL');
    }
}
