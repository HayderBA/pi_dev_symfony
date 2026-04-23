<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260421101000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add evenement and reservation tables for the integrated event and reservation module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE evenement (idEvenement INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, date DATE NOT NULL, localisation VARCHAR(100) NOT NULL, popularity_score DOUBLE PRECISION DEFAULT 0, predicted_attendance INT DEFAULT 0, dynamic_price DOUBLE PRECISION DEFAULT 50, base_price DOUBLE PRECISION DEFAULT 50, max_capacity INT DEFAULT 100, venue_layout VARCHAR(50) DEFAULT 'standard', is_dynamic_price_active TINYINT(1) DEFAULT 0, seat_categories JSON DEFAULT NULL, PRIMARY KEY(idEvenement)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE reservation (idReservation INT AUTO_INCREMENT NOT NULL, idEvenement INT NOT NULL, nom VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, telephone VARCHAR(20) NOT NULL, nombre_personnes INT DEFAULT 1 NOT NULL, date_reservation DATETIME NOT NULL, fraud_probability DOUBLE PRECISION DEFAULT 0, is_suspicious TINYINT(1) DEFAULT 0, allocated_seats LONGTEXT DEFAULT NULL, seating_preference VARCHAR(20) DEFAULT 'auto', qr_code LONGTEXT DEFAULT NULL, seat_number VARCHAR(10) DEFAULT NULL, INDEX IDX_42C84955A6731D90 (idEvenement), PRIMARY KEY(idReservation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A6731D90 FOREIGN KEY (idEvenement) REFERENCES evenement (idEvenement) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A6731D90');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE evenement');
    }
}
