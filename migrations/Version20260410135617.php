<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260410135617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reponses (id INT AUTO_INCREMENT NOT NULL, auteur VARCHAR(100) NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, likes INT NOT NULL, dislikes INT NOT NULL, forum_post_id INT NOT NULL, INDEX IDX_1E512EC6BA454E5D (forum_post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC6BA454E5D FOREIGN KEY (forum_post_id) REFERENCES forum_posts (id)');
        $this->addSql('DROP TABLE users');
        $this->addSql('ALTER TABLE forum_posts DROP FOREIGN KEY `forum_posts_ibfk_1`');
        $this->addSql('DROP INDEX user_id ON forum_posts');
        $this->addSql('ALTER TABLE forum_posts DROP user_id, CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE date_creation date_creation DATETIME NOT NULL, CHANGE archive archive TINYINT NOT NULL, CHANGE likes likes INT NOT NULL, CHANGE vues vues INT NOT NULL, CHANGE dislikes dislikes INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, second_name VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, age INT NOT NULL, gender VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, phone_number INT NOT NULL, birth_date VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, phone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, is_blocked TINYINT DEFAULT 0, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC6BA454E5D');
        $this->addSql('DROP TABLE reponses');
        $this->addSql('ALTER TABLE forum_posts ADD user_id INT DEFAULT NULL, CHANGE contenu contenu TEXT NOT NULL, CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE archive archive TINYINT DEFAULT 0, CHANGE likes likes INT DEFAULT 0, CHANGE vues vues INT DEFAULT 0, CHANGE dislikes dislikes INT DEFAULT 0');
        $this->addSql('ALTER TABLE forum_posts ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX user_id ON forum_posts (user_id)');
    }
}
