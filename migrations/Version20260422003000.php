<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422003000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add forum posts and responses tables for GrowMind forum integration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE forum_posts (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, role VARCHAR(50) NOT NULL, categorie VARCHAR(100) NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', archive TINYINT(1) NOT NULL, likes INT NOT NULL, vues INT NOT NULL, dislikes INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponses (id INT AUTO_INCREMENT NOT NULL, forum_post_id INT NOT NULL, auteur VARCHAR(100) NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', likes INT NOT NULL, dislikes INT NOT NULL, INDEX IDX_1E512EC6BA454E5D (forum_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC6BA454E5D FOREIGN KEY (forum_post_id) REFERENCES forum_posts (id) ON DELETE CASCADE');
        $this->addSql("INSERT INTO forum_posts (id, nom, role, categorie, contenu, date_creation, archive, likes, vues, dislikes) VALUES
            (2, 'sarra', 'medecin', 'Anxiete', 'Je partage ici un premier sujet de demonstration pour integrer le forum dans GrowMind.', '2026-04-10 15:52:41', 0, 0, 5, 0),
            (3, 'ahmed', 'patient', 'General', 'Ce sujet sert de base simple pour verifier que le module forum fonctionne bien dans la meme application.', '2026-04-10 15:58:11', 0, 0, 9, 0)");
        $this->addSql("INSERT INTO reponses (id, auteur, contenu, date_creation, likes, dislikes, forum_post_id) VALUES
            (1, 'sarra', 'Bienvenue dans le forum integre a GrowMind.', '2026-04-10 16:04:39', 0, 0, 3)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC6BA454E5D');
        $this->addSql('DROP TABLE reponses');
        $this->addSql('DROP TABLE forum_posts');
    }
}
