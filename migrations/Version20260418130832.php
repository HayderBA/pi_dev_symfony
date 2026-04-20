<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260418130832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, second_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, passwd VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('DROP TABLE post_vote');
        $this->addSql('DROP TABLE users');
        $this->addSql('ALTER TABLE conversation DROP user_id, CHANGE is_ia is_ia TINYINT NOT NULL');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_MESSAGE_CONVERSATION`');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_MESSAGE_SENDER`');
        $this->addSql('DROP INDEX IDX_SENDER ON message');
        $this->addSql('DROP INDEX IDX_CONVERSATION ON message');
        $this->addSql('ALTER TABLE message DROP conversation_id, DROP sender_id, DROP content, DROP created_at, DROP is_read, DROP is_urgent, DROP ia_responded');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_vote (id INT AUTO_INCREMENT NOT NULL, forum_post_id INT NOT NULL, INDEX IDX_9345E26FBA454E5D (forum_post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, second_name VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, age INT NOT NULL, gender VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, phone_number INT NOT NULL, birth_date VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, phone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, is_blocked TINYINT NOT NULL, is_verified TINYINT NOT NULL, medecin_id INT DEFAULT NULL, qr_token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, qr_expires_at DATETIME DEFAULT NULL, INDEX IDX_1483A5E94F31A84 (medecin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE conversation ADD user_id INT DEFAULT NULL, CHANGE is_ia is_ia TINYINT DEFAULT 0');
        $this->addSql('ALTER TABLE message ADD conversation_id INT NOT NULL, ADD sender_id INT DEFAULT NULL, ADD content LONGTEXT NOT NULL, ADD created_at DATETIME NOT NULL, ADD is_read TINYINT DEFAULT 0, ADD is_urgent TINYINT DEFAULT 0, ADD ia_responded TINYINT DEFAULT 0');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_MESSAGE_CONVERSATION` FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_MESSAGE_SENDER` FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_SENDER ON message (sender_id)');
        $this->addSql('CREATE INDEX IDX_CONVERSATION ON message (conversation_id)');
    }
}
