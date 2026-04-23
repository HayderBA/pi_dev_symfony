<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add solidarity donation fields to reservation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation ADD solidarity_association VARCHAR(150) DEFAULT NULL, ADD solidarity_amount DOUBLE PRECISION DEFAULT 0, ADD solidarity_receipt_code VARCHAR(60) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP solidarity_association, DROP solidarity_amount, DROP solidarity_receipt_code');
    }
}
