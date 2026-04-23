<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422013000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add users, alerts, chat, quiz and feedback structures required by the full forum module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE users (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            second_name VARCHAR(100) NOT NULL,
            age INT DEFAULT NULL,
            gender VARCHAR(20) DEFAULT NULL,
            phone_number INT DEFAULT NULL,
            birth_date VARCHAR(20) DEFAULT NULL,
            email VARCHAR(180) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            is_blocked TINYINT(1) NOT NULL DEFAULT 0,
            is_verified TINYINT(1) NOT NULL DEFAULT 0,
            medecin_id INT DEFAULT NULL,
            qr_token VARCHAR(255) DEFAULT NULL,
            qr_expires_at DATETIME DEFAULT NULL,
            latitude DECIMAL(10,8) DEFAULT NULL,
            longitude DECIMAL(11,8) DEFAULT NULL,
            adresse VARCHAR(255) DEFAULT NULL,
            fcm_token VARCHAR(255) DEFAULT NULL,
            telegram_chat_id VARCHAR(255) DEFAULT NULL,
            INDEX IDX_1483A5E94F31A84 (medecin_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE alerte (
            id INT AUTO_INCREMENT NOT NULL,
            distance NUMERIC(10, 2) NOT NULL,
            latitude NUMERIC(10, 8) NOT NULL,
            longitude NUMERIC(11, 8) NOT NULL,
            statut VARCHAR(50) NOT NULL,
            patient_nom VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            medecin_id INT NOT NULL,
            patient_id INT NOT NULL,
            medecin_nom VARCHAR(100) NOT NULL,
            INDEX IDX_3AE753A4F31A84 (medecin_id),
            INDEX IDX_3AE753A6B899279 (patient_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE conversation (
            id INT AUTO_INCREMENT NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            is_ia TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE conversation_participant (
            conversation_id INT NOT NULL,
            user_id INT NOT NULL,
            INDEX IDX_398016619AC0396 (conversation_id),
            INDEX IDX_39801661A76ED395 (user_id),
            PRIMARY KEY(conversation_id, user_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE message (
            id INT AUTO_INCREMENT NOT NULL,
            conversation_id INT NOT NULL,
            sender_id INT DEFAULT NULL,
            content LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            is_urgent TINYINT(1) NOT NULL DEFAULT 0,
            ia_responded TINYINT(1) NOT NULL DEFAULT 0,
            INDEX IDX_B6BD307F9AC0396 (conversation_id),
            INDEX IDX_B6BD307FF624B39D (sender_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE quiz_reponse (
            id INT AUTO_INCREMENT NOT NULL,
            score INT NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            user_id INT NOT NULL,
            INDEX IDX_4DEC7367A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE feedback (
            id INT AUTO_INCREMENT NOT NULL,
            note INT NOT NULL,
            message LONGTEXT DEFAULT NULL,
            creat_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            user_id INT NOT NULL,
            INDEX IDX_D2294458A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753A4F31A84 FOREIGN KEY (medecin_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753A6B899279 FOREIGN KEY (patient_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_398016619AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_39801661A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE quiz_reponse ADD CONSTRAINT FK_4DEC7367A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D2294458A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql("INSERT INTO users (name, second_name, age, gender, phone_number, birth_date, email, password, role, phone, is_blocked, is_verified, latitude, longitude, adresse)
            VALUES
            ('Hamadi', 'Hamadi', 20, 'male', 24269512, '2006-02-10', 'patient@growmind.com', '', 'patient', '24269512', 0, 1, 36.90199813, 10.18686236, 'Ariana'),
            ('Sarah', 'Ben Ahmed', 38, 'female', 23456789, '1986-05-15', 'dr.sarah@growmind.com', '', 'medecin', '23456789', 0, 1, 36.86000000, 10.19500000, 'Ariana'),
            ('Mohamed', 'Ben Ali', 45, 'male', 12345678, '1979-01-01', 'dr.mohamed@growmind.com', '', 'medecin', '12345678', 0, 1, 36.80650000, 10.18150000, 'Tunis Centre')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753A4F31A84');
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753A6B899279');
        $this->addSql('ALTER TABLE conversation_participant DROP FOREIGN KEY FK_398016619AC0396');
        $this->addSql('ALTER TABLE conversation_participant DROP FOREIGN KEY FK_39801661A76ED395');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE quiz_reponse DROP FOREIGN KEY FK_4DEC7367A76ED395');
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458A76ED395');
        $this->addSql('DROP TABLE feedback');
        $this->addSql('DROP TABLE quiz_reponse');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE conversation_participant');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE alerte');
        $this->addSql('DROP TABLE users');
    }
}
