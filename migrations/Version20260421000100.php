<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260421000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create programme_sportif table for generated sport plans.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE programme_sportif (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, age INT NOT NULL, genre VARCHAR(10) NOT NULL, taille_cm DOUBLE PRECISION NOT NULL, poids_kg DOUBLE PRECISION NOT NULL, niveau_stress INT NOT NULL, qualite_sommeil INT NOT NULL, duree_sommeil_heures DOUBLE PRECISION NOT NULL, niveau_activite VARCHAR(20) NOT NULL, objectif VARCHAR(30) NOT NULL, activite_cible VARCHAR(80) NOT NULL, imc DOUBLE PRECISION DEFAULT NULL, categorie_imc VARCHAR(30) DEFAULT NULL, source_imc VARCHAR(50) DEFAULT NULL, besoin_calorique INT DEFAULT NULL, calories_activite INT DEFAULT NULL, source_calories VARCHAR(50) DEFAULT NULL, intensite VARCHAR(30) DEFAULT NULL, type_programme VARCHAR(60) DEFAULT NULL, resume LONGTEXT DEFAULT NULL, seances JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_programme_user_created (user_id, created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE programme_sportif ADD CONSTRAINT FK_PROGRAMME_SPORTIF_USER FOREIGN KEY (user_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE programme_sportif');
    }
}
