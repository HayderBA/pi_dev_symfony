<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422101000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default cabinet and psychologues from existing users doctors when cabinet module is empty';
    }

    public function up(Schema $schema): void
    {
        $connection = $this->connection;

        $cabinetCount = (int) $connection->fetchOne('SELECT COUNT(*) FROM cabinet');
        if (0 === $cabinetCount) {
            $connection->insert('cabinet', [
                'nomcabinet' => 'Cabinet GrowMind',
                'adresse' => 'Centre Medical GrowMind',
                'ville' => 'Tunis',
                'telephone' => '20000000',
                'email' => 'cabinet@growmind.com',
                'description' => 'Cabinet principal genere automatiquement pour integrer les medecins existants.',
                'status' => 'actif',
            ]);
        }

        $cabinetId = (int) $connection->fetchOne('SELECT idCabinet FROM cabinet ORDER BY idCabinet ASC LIMIT 1');
        if (0 === $cabinetId) {
            return;
        }

        $psychologueCount = (int) $connection->fetchOne('SELECT COUNT(*) FROM psychologue');
        if (0 !== $psychologueCount) {
            return;
        }

        $doctors = $connection->fetchAllAssociative("SELECT id, name, second_name, email, phone, phone_number FROM users WHERE LOWER(role) IN ('medecin','doctor') ORDER BY id ASC");

        foreach ($doctors as $doctor) {
            $telephone = (string) ($doctor['phone'] ?: $doctor['phone_number'] ?: '20000000');
            $connection->insert('psychologue', [
                'idCabinet' => $cabinetId,
                'user_id' => (int) $doctor['id'],
                'nom' => (string) ($doctor['second_name'] ?: 'Medecin'),
                'prenom' => (string) ($doctor['name'] ?: 'GrowMind'),
                'specialite' => 'Psychologie clinique',
                'diplome' => 'Doctorat',
                'experience' => 5,
                'tarif' => 100,
                'email' => (string) ($doctor['email'] ?: 'medecin@growmind.com'),
                'telephone' => $telephone,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM psychologue WHERE user_id IS NOT NULL AND specialite = 'Psychologie clinique' AND diplome = 'Doctorat'");
        $this->addSql("DELETE FROM cabinet WHERE nomcabinet = 'Cabinet GrowMind'");
    }
}
