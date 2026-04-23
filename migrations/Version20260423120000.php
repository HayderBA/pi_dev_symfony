<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add legacy users/admins/doctors/patients compatibility tables and sync psychologues with cabinet links';
    }

    public function up(Schema $schema): void
    {
        $connection = $this->connection;
        $schemaManager = $connection->createSchemaManager();
        $tables = array_map('strtolower', $schemaManager->listTableNames());

        $this->abortIf(!in_array('users', $tables, true), 'The users table must exist before running this migration.');

        $this->dropLegacyMedecinId($connection, $schemaManager);
        $this->ensureLegacyTables($connection, $tables);
        $this->ensureDefaultCabinet($connection, $tables);
        $this->syncLegacyUsers($connection);
        $this->syncLegacyProfiles($connection);
        $this->syncPsychologues($connection);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS admins');
        $this->addSql('DROP TABLE IF EXISTS doctors');
        $this->addSql('DROP TABLE IF EXISTS patients');
    }

    private function dropLegacyMedecinId(Connection $connection, $schemaManager): void
    {
        $columns = $schemaManager->listTableColumns('users');
        if (!isset($columns['medecin_id'])) {
            return;
        }

        $foreignKeys = $connection->fetchFirstColumn("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = 'medecin_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignKeys as $foreignKey) {
            $connection->executeStatement(sprintf('ALTER TABLE users DROP FOREIGN KEY `%s`', $foreignKey));
        }

        $indexes = $connection->fetchFirstColumn("
            SELECT DISTINCT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = 'medecin_id'
              AND INDEX_NAME <> 'PRIMARY'
        ");

        foreach ($indexes as $index) {
            $connection->executeStatement(sprintf('ALTER TABLE users DROP INDEX `%s`', $index));
        }

        $connection->executeStatement('ALTER TABLE users DROP COLUMN medecin_id');
    }

    private function ensureLegacyTables(Connection $connection, array $tables): void
    {
        if (!in_array('admins', $tables, true)) {
            $connection->executeStatement('
                CREATE TABLE admins (
                    id_user INT NOT NULL,
                    actif TINYINT(1) NOT NULL DEFAULT 1,
                    face_image VARCHAR(255) DEFAULT NULL,
                    PRIMARY KEY(id_user),
                    CONSTRAINT FK_LEGACY_ADMIN_USER FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE CASCADE
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            ');
        }

        if (!in_array('doctors', $tables, true)) {
            $connection->executeStatement('
                CREATE TABLE doctors (
                    id_user INT NOT NULL,
                    specialty VARCHAR(120) DEFAULT NULL,
                    experience INT DEFAULT 0,
                    diplome VARCHAR(200) DEFAULT NULL,
                    disponible TINYINT(1) NOT NULL DEFAULT 1,
                    tarif_consultation DOUBLE PRECISION DEFAULT NULL,
                    actif TINYINT(1) NOT NULL DEFAULT 1,
                    PRIMARY KEY(id_user),
                    CONSTRAINT FK_LEGACY_DOCTOR_USER FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE CASCADE
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            ');
        }

        if (!in_array('patients', $tables, true)) {
            $connection->executeStatement('
                CREATE TABLE patients (
                    id_user INT NOT NULL,
                    blood_type VARCHAR(30) DEFAULT NULL,
                    weight FLOAT DEFAULT NULL,
                    height FLOAT DEFAULT NULL,
                    PRIMARY KEY(id_user),
                    CONSTRAINT FK_LEGACY_PATIENT_USER FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE CASCADE
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            ');
        }
    }

    private function ensureDefaultCabinet(Connection $connection, array $tables): void
    {
        if (!in_array('cabinet', $tables, true)) {
            return;
        }

        $cabinetCount = (int) $connection->fetchOne('SELECT COUNT(*) FROM cabinet');
        if ($cabinetCount > 0) {
            return;
        }

        $connection->insert('cabinet', [
            'nomcabinet' => 'Cabinet GrowMind',
            'adresse' => 'Centre Medical GrowMind',
            'ville' => 'Tunis',
            'telephone' => '20000000',
            'email' => 'cabinet@growmind.com',
            'description' => 'Cabinet principal pour synchroniser les profils psychologues existants.',
            'status' => 'actif',
        ]);
    }

    private function syncLegacyUsers(Connection $connection): void
    {
        $users = [
            [
                'id' => 39,
                'name' => 'Hayder',
                'second_name' => 'ahmed',
                'age' => 20,
                'gender' => 'male',
                'phone_number' => 12345678,
                'birth_date' => '2026-02-07',
                'email' => 'a@a.a',
                'password' => '',
                'role' => 'doctor',
                'phone' => null,
                'is_blocked' => 0,
                'is_verified' => 1,
                'google_authenticator_secret' => null,
                'face_image' => null,
            ],
            [
                'id' => 48,
                'name' => 'ahmed',
                'second_name' => 'ahmed',
                'age' => 30,
                'gender' => 'male',
                'phone_number' => 2147483647,
                'birth_date' => '2026-04-02',
                'email' => 'benahmedhayder10@gmail.com',
                'password' => '$2y$13$OyDOBun4WvUjpenPl7A5XOFK0DP5tuN49L6UehhvxLRUCftSIsEjO',
                'role' => 'doctor',
                'phone' => null,
                'is_blocked' => 0,
                'is_verified' => 1,
                'google_authenticator_secret' => null,
                'face_image' => null,
            ],
            [
                'id' => 49,
                'name' => 'mondher',
                'second_name' => 'mondher',
                'age' => 19,
                'gender' => 'Homme',
                'phone_number' => 12345678,
                'birth_date' => '12/12/2004',
                'email' => 'm@m.m',
                'password' => '$2y$13$8hZfKmpG6bt1DmxKQ3Chq.q7Kh0LteszEMopcYqsu10H8vxeCxG8i',
                'role' => 'admin',
                'phone' => null,
                'is_blocked' => 0,
                'is_verified' => 1,
                'google_authenticator_secret' => null,
                'face_image' => 'faces/admin_49.jpg',
            ],
            [
                'id' => 56,
                'name' => 'rahma',
                'second_name' => 'rahma',
                'age' => 20,
                'gender' => 'female',
                'phone_number' => 2147483647,
                'birth_date' => '2026-04-02',
                'email' => 'r@r.r',
                'password' => '$2y$13$7bZYB2pQ0x4um4vBJxh2yOPVK.nS/YTDUzDSh6Tlcha6Idza0s3mS',
                'role' => 'patient',
                'phone' => null,
                'is_blocked' => 0,
                'is_verified' => 1,
                'google_authenticator_secret' => 'KXJ7RYNB6XGZW6NGPW6DYRJTNB2BGMUDWUFCZ57JDI7BG7VCJQ7A',
                'face_image' => null,
            ],
            [
                'id' => 57,
                'name' => 'sophie',
                'second_name' => 'sophie',
                'age' => 20,
                'gender' => 'female',
                'phone_number' => 2147483647,
                'birth_date' => '2026-04-02',
                'email' => 's@s.s',
                'password' => '$2y$13$2A/M8Bp.TSGEkbB0DrAvJOp8eJoAhQUHU0.gjOOgekpUmVDjeAJ1e',
                'role' => 'patient',
                'phone' => null,
                'is_blocked' => 0,
                'is_verified' => 1,
                'google_authenticator_secret' => 'ADIXLL2VVJLROBSUSQPDSQXTLSZODCIGKCKBL34CEQPWRSCQ7N2A',
                'face_image' => null,
            ],
            [
                'id' => 58,
                'name' => 'Ali',
                'second_name' => 'Ben Salah',
                'age' => 22,
                'gender' => 'male',
                'phone_number' => 12345678,
                'birth_date' => '2002-05-10',
                'email' => 'ali@email.com',
                'password' => '$2y$13$I/4vrtq4RDWDYh/MI3wGP.U.sihEiYJ5Z2PeUXKFwS5d5ubKQDoTG',
                'role' => 'admin',
                'phone' => null,
                'is_blocked' => 0,
                'is_verified' => 1,
                'google_authenticator_secret' => null,
                'face_image' => 'faces/admin_58.jpg',
            ],
            [
                'id' => 59,
                'name' => 'sophie',
                'second_name' => 'sophie',
                'age' => 20,
                'gender' => 'female',
                'phone_number' => 2147483647,
                'birth_date' => '2026-04-02',
                'email' => 'so@so.so',
                'password' => '$2y$13$gBmqSjGFkREabAK2uw5HyObqMTjShFzdTlCc6f2ewjSe/glZ9XazG',
                'role' => 'patient',
                'phone' => null,
                'is_blocked' => 0,
                'is_verified' => 1,
                'google_authenticator_secret' => 'GMBRPTE34GZ4FWHKV3ZKLEJDLN3SHVWB3G3GF2ZTGWU2NMSBNLTA',
                'face_image' => null,
            ],
        ];

        foreach ($users as $user) {
            $existingId = $connection->fetchOne('SELECT id FROM users WHERE id = ?', [$user['id']]);

            if ($existingId) {
                $connection->update('users', [
                    'name' => $user['name'],
                    'second_name' => $user['second_name'],
                    'age' => $user['age'],
                    'gender' => $user['gender'],
                    'phone_number' => $user['phone_number'],
                    'birth_date' => $user['birth_date'],
                    'email' => $user['email'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'phone' => $user['phone'],
                    'is_blocked' => $user['is_blocked'],
                    'is_verified' => $user['is_verified'],
                    'google_authenticator_secret' => $user['google_authenticator_secret'],
                    'face_image' => $user['face_image'],
                ], ['id' => $user['id']]);

                continue;
            }

            $existingByEmail = $connection->fetchOne('SELECT id FROM users WHERE email = ?', [$user['email']]);
            if ($existingByEmail) {
                $connection->update('users', [
                    'name' => $user['name'],
                    'second_name' => $user['second_name'],
                    'age' => $user['age'],
                    'gender' => $user['gender'],
                    'phone_number' => $user['phone_number'],
                    'birth_date' => $user['birth_date'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'phone' => $user['phone'],
                    'is_blocked' => $user['is_blocked'],
                    'is_verified' => $user['is_verified'],
                    'google_authenticator_secret' => $user['google_authenticator_secret'],
                    'face_image' => $user['face_image'],
                ], ['id' => $existingByEmail]);

                continue;
            }

            $connection->insert('users', $user);
        }
    }

    private function syncLegacyProfiles(Connection $connection): void
    {
        $admins = [
            ['id_user' => 49, 'actif' => 1, 'face_image' => 'faces/admin_49.jpg'],
            ['id_user' => 58, 'actif' => 1, 'face_image' => 'faces/admin_58.jpg'],
        ];

        foreach ($admins as $admin) {
            $this->upsertById($connection, 'admins', $admin, 'id_user');
            $connection->update('users', [
                'role' => 'admin',
                'face_image' => $admin['face_image'],
            ], ['id' => $admin['id_user']]);
        }

        $doctors = [
            ['id_user' => 39, 'specialty' => 'apapap', 'experience' => 3, 'diplome' => 'azer', 'disponible' => 1, 'tarif_consultation' => 291.0, 'actif' => 1],
            ['id_user' => 48, 'specialty' => 'ahmed', 'experience' => 14, 'diplome' => 'hayder', 'disponible' => 1, 'tarif_consultation' => 90.0, 'actif' => 1],
        ];

        foreach ($doctors as $doctor) {
            $this->upsertById($connection, 'doctors', $doctor, 'id_user');
            $connection->update('users', ['role' => 'doctor'], ['id' => $doctor['id_user']]);
        }

        $patients = [
            ['id_user' => 56, 'blood_type' => 'A+', 'weight' => 120.0, 'height' => 120.0],
            ['id_user' => 57, 'blood_type' => 'A+', 'weight' => 100.0, 'height' => 120.0],
            ['id_user' => 59, 'blood_type' => 'A-', 'weight' => 120.0, 'height' => 120.0],
        ];

        foreach ($patients as $patient) {
            $this->upsertById($connection, 'patients', $patient, 'id_user');
            $connection->update('users', ['role' => 'patient'], ['id' => $patient['id_user']]);
        }
    }

    private function syncPsychologues(Connection $connection): void
    {
        $cabinetId = (int) $connection->fetchOne("SELECT idCabinet FROM cabinet WHERE status = 'actif' ORDER BY idCabinet ASC LIMIT 1");
        if (0 === $cabinetId) {
            $cabinetId = (int) $connection->fetchOne('SELECT idCabinet FROM cabinet ORDER BY idCabinet ASC LIMIT 1');
        }

        if (0 === $cabinetId) {
            return;
        }

        $doctors = $connection->fetchAllAssociative('
            SELECT d.id_user, d.specialty, d.experience, d.diplome, d.tarif_consultation, u.name, u.second_name, u.email, u.phone, u.phone_number
            FROM doctors d
            INNER JOIN users u ON u.id = d.id_user
            ORDER BY d.id_user ASC
        ');

        foreach ($doctors as $doctor) {
            $userId = (int) $doctor['id_user'];
            $data = [
                'idCabinet' => $cabinetId,
                'user_id' => $userId,
                'nom' => (string) ($doctor['second_name'] ?: 'Medecin'),
                'prenom' => (string) ($doctor['name'] ?: 'GrowMind'),
                'specialite' => (string) ($doctor['specialty'] ?: 'Psychologie clinique'),
                'diplome' => (string) ($doctor['diplome'] ?: 'Doctorat'),
                'experience' => (int) ($doctor['experience'] ?? 0),
                'tarif' => (float) ($doctor['tarif_consultation'] ?? 0),
                'email' => (string) ($doctor['email'] ?: 'medecin@growmind.com'),
                'telephone' => (string) ($doctor['phone'] ?: $doctor['phone_number'] ?: '20000000'),
            ];

            $psychologueId = $connection->fetchOne('SELECT idPsychologue FROM psychologue WHERE user_id = ?', [$userId]);

            if ($psychologueId) {
                $connection->update('psychologue', $data, ['idPsychologue' => $psychologueId]);
                continue;
            }

            $connection->insert('psychologue', $data);
        }
    }

    private function upsertById(Connection $connection, string $table, array $data, string $idColumn): void
    {
        $existing = $connection->fetchOne(sprintf('SELECT %s FROM %s WHERE %s = ?', $idColumn, $table, $idColumn), [$data[$idColumn]]);

        if ($existing) {
            $payload = $data;
            unset($payload[$idColumn]);
            $connection->update($table, $payload, [$idColumn => $data[$idColumn]]);

            return;
        }

        $connection->insert($table, $data);
    }
}
