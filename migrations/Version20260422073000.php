<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422073000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user management 2FA, Face ID, and mental health check storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD google_authenticator_secret VARCHAR(255) DEFAULT NULL, ADD face_image VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TABLE mental_health_check (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, checked_at DATETIME NOT NULL, games_data LONGTEXT DEFAULT NULL, ai_result VARCHAR(50) NOT NULL, ai_advice LONGTEXT DEFAULT NULL, ai_score INT NOT NULL, INDEX IDX_MENTAL_HEALTH_PATIENT (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mental_health_check ADD CONSTRAINT FK_MENTAL_HEALTH_PATIENT FOREIGN KEY (patient_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mental_health_check DROP FOREIGN KEY FK_MENTAL_HEALTH_PATIENT');
        $this->addSql('DROP TABLE mental_health_check');
        $this->addSql('ALTER TABLE users DROP google_authenticator_secret, DROP face_image');
    }
}
