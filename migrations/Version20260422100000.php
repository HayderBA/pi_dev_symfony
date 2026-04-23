<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cabinet, psychologue and rendezvous management linked to existing users doctors';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cabinet (idCabinet INT AUTO_INCREMENT NOT NULL, nomcabinet VARCHAR(100) NOT NULL, adresse VARCHAR(255) NOT NULL, ville VARCHAR(100) NOT NULL, telephone VARCHAR(30) NOT NULL, email VARCHAR(180) NOT NULL, description VARCHAR(1000) NOT NULL, status VARCHAR(50) NOT NULL, PRIMARY KEY(idCabinet)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE psychologue (idPsychologue INT AUTO_INCREMENT NOT NULL, idCabinet INT DEFAULT NULL, user_id INT DEFAULT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, specialite VARCHAR(120) NOT NULL, diplome VARCHAR(120) NOT NULL, experience INT NOT NULL, tarif DOUBLE PRECISION NOT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(30) NOT NULL, INDEX IDX_76D2D8885089F88D (idCabinet), INDEX IDX_76D2D888A76ED395 (user_id), PRIMARY KEY(idPsychologue)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendezvous (idRdv INT AUTO_INCREMENT NOT NULL, idPsychologue INT NOT NULL, dateRdv DATE NOT NULL, heure TIME NOT NULL, statut VARCHAR(50) NOT NULL, typeCons VARCHAR(30) NOT NULL, telephone_patient VARCHAR(20) DEFAULT NULL, email_patient VARCHAR(100) DEFAULT NULL, nom_patient VARCHAR(50) DEFAULT NULL, prenom_patient VARCHAR(50) DEFAULT NULL, rappel_envoye TINYINT(1) DEFAULT NULL, date_rappel DATETIME DEFAULT NULL, est_paye TINYINT(1) DEFAULT NULL, stripe_session_id VARCHAR(255) DEFAULT NULL, montant DOUBLE PRECISION DEFAULT NULL, tarif_consultation DOUBLE PRECISION DEFAULT NULL, INDEX IDX_AEA349DB93226DB2 (idPsychologue), PRIMARY KEY(idRdv)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE psychologue ADD CONSTRAINT FK_76D2D8885089F88D FOREIGN KEY (idCabinet) REFERENCES cabinet (idCabinet) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE psychologue ADD CONSTRAINT FK_76D2D888A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT FK_AEA349DB93226DB2 FOREIGN KEY (idPsychologue) REFERENCES psychologue (idPsychologue) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY FK_AEA349DB93226DB2');
        $this->addSql('ALTER TABLE psychologue DROP FOREIGN KEY FK_76D2D8885089F88D');
        $this->addSql('ALTER TABLE psychologue DROP FOREIGN KEY FK_76D2D888A76ED395');
        $this->addSql('DROP TABLE rendezvous');
        $this->addSql('DROP TABLE psychologue');
        $this->addSql('DROP TABLE cabinet');
    }
}
