<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260421224247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE feedback (id INT AUTO_INCREMENT NOT NULL, note INT NOT NULL, message LONGTEXT DEFAULT NULL, creat_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_D2294458A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D2294458A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE conversation DROP user_id, DROP participants, CHANGE is_ia is_ia TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE conversation_participant DROP FOREIGN KEY `conversation_participant_ibfk_2`');
        $this->addSql('ALTER TABLE conversation_participant DROP FOREIGN KEY `conversation_participant_ibfk_2`');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_39801661A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX user_id ON conversation_participant');
        $this->addSql('CREATE INDEX IDX_39801661A76ED395 ON conversation_participant (user_id)');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT `conversation_participant_ibfk_2` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_MESSAGE_CONVERSATION`');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_MESSAGE_SENDER`');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_MESSAGE_CONVERSATION`');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_MESSAGE_SENDER`');
        $this->addSql('ALTER TABLE message DROP ia_responded, CHANGE sender_id sender_id INT NOT NULL, CHANGE is_read is_read TINYINT DEFAULT 0 NOT NULL, CHANGE is_urgent is_urgent TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES users (id)');
        $this->addSql('DROP INDEX idx_conversation ON message');
        $this->addSql('CREATE INDEX IDX_B6BD307F9AC0396 ON message (conversation_id)');
        $this->addSql('DROP INDEX idx_sender ON message');
        $this->addSql('CREATE INDEX IDX_B6BD307FF624B39D ON message (sender_id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_MESSAGE_CONVERSATION` FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_MESSAGE_SENDER` FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE quiz_reponse DROP FOREIGN KEY `FK_QUIZ_USER`');
        $this->addSql('DROP INDEX IDX_1483A5E94F31A84 ON users');
        $this->addSql('ALTER TABLE users DROP age, DROP gender, DROP phone_number, DROP birth_date, DROP is_blocked, DROP is_verified, DROP medecin_id, DROP qr_token, DROP qr_expires_at, CHANGE name name VARCHAR(100) NOT NULL, CHANGE second_name second_name VARCHAR(100) NOT NULL, CHANGE email email VARCHAR(180) NOT NULL, CHANGE role role VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, second_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, passwd VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, latitude NUMERIC(10, 8) DEFAULT NULL, longitude NUMERIC(11, 8) DEFAULT NULL, adresse VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458A76ED395');
        $this->addSql('DROP TABLE feedback');
        $this->addSql('ALTER TABLE conversation ADD user_id INT DEFAULT NULL, ADD participants VARCHAR(255) DEFAULT NULL, CHANGE is_ia is_ia TINYINT DEFAULT 0');
        $this->addSql('ALTER TABLE conversation_participant DROP FOREIGN KEY FK_39801661A76ED395');
        $this->addSql('ALTER TABLE conversation_participant DROP FOREIGN KEY FK_39801661A76ED395');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT `conversation_participant_ibfk_2` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_39801661a76ed395 ON conversation_participant');
        $this->addSql('CREATE INDEX user_id ON conversation_participant (user_id)');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_39801661A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message ADD ia_responded TINYINT DEFAULT 0, CHANGE is_read is_read TINYINT DEFAULT 0, CHANGE is_urgent is_urgent TINYINT DEFAULT 0, CHANGE sender_id sender_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_MESSAGE_CONVERSATION` FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_MESSAGE_SENDER` FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('DROP INDEX idx_b6bd307f9ac0396 ON message');
        $this->addSql('CREATE INDEX IDX_CONVERSATION ON message (conversation_id)');
        $this->addSql('DROP INDEX idx_b6bd307ff624b39d ON message');
        $this->addSql('CREATE INDEX IDX_SENDER ON message (sender_id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE quiz_reponse ADD CONSTRAINT `FK_QUIZ_USER` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD age INT NOT NULL, ADD gender VARCHAR(20) NOT NULL, ADD phone_number INT NOT NULL, ADD birth_date VARCHAR(20) NOT NULL, ADD is_blocked TINYINT NOT NULL, ADD is_verified TINYINT NOT NULL, ADD medecin_id INT DEFAULT NULL, ADD qr_token VARCHAR(255) DEFAULT NULL, ADD qr_expires_at DATETIME DEFAULT NULL, CHANGE name name VARCHAR(20) NOT NULL, CHANGE second_name second_name VARCHAR(20) NOT NULL, CHANGE email email VARCHAR(50) NOT NULL, CHANGE role role VARCHAR(20) NOT NULL');
        $this->addSql('CREATE INDEX IDX_1483A5E94F31A84 ON users (medecin_id)');
    }
}
