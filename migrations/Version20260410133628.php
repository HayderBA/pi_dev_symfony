<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260410133628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, auteur VARCHAR(100) NOT NULL, contenu LONGTEXT NOT NULL, datecreation DATETIME NOT NULL, likes INT NOT NULL, forumpost_id INT NOT NULL, INDEX IDX_5FB6DEC7AA15D77E (forumpost_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7AA15D77E FOREIGN KEY (forumpost_id) REFERENCES forum_posts (id)');
        $this->addSql('ALTER TABLE admins DROP FOREIGN KEY `fk_user_admin`');
        $this->addSql('ALTER TABLE alertes_urgence DROP FOREIGN KEY `alertes_ibfk_user`');
        $this->addSql('ALTER TABLE conseils_utilisateurs DROP FOREIGN KEY `conseils_utilisateurs_ibfk_1`');
        $this->addSql('ALTER TABLE conseils_utilisateurs DROP FOREIGN KEY `conseils_utilisateurs_ibfk_2`');
        $this->addSql('ALTER TABLE doctors DROP FOREIGN KEY `fk_user_doctor`');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY `evaluation_ibfk_1`');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY `evaluation_ibfk_2`');
        $this->addSql('ALTER TABLE favori DROP FOREIGN KEY `favori_ibfk_1`');
        $this->addSql('ALTER TABLE favori DROP FOREIGN KEY `favori_ibfk_2`');
        $this->addSql('ALTER TABLE humeurs DROP FOREIGN KEY `humeurs_ibfk_1`');
        $this->addSql('ALTER TABLE patients DROP FOREIGN KEY `fk_user_patient`');
        $this->addSql('ALTER TABLE psychologue DROP FOREIGN KEY `psychologue_ibfk_1`');
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY `rendezvous_ibfk_1`');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY `reponses_ibfk_1`');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY `reponses_ibfk_2`');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY `reservation_ibfk_1`');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY `reservation_ibfk_2`');
        $this->addSql('ALTER TABLE sante_bien_etre DROP FOREIGN KEY `sante_bien_etre_ibfk_1`');
        $this->addSql('ALTER TABLE sleep_tracking DROP FOREIGN KEY `sleep_tracking_ibfk_1`');
        $this->addSql('ALTER TABLE tests DROP FOREIGN KEY `tests_ibfk_1`');
        $this->addSql('DROP TABLE admins');
        $this->addSql('DROP TABLE alertes_urgence');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE cabinet');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE conseils');
        $this->addSql('DROP TABLE conseils_utilisateurs');
        $this->addSql('DROP TABLE doctors');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE favori');
        $this->addSql('DROP TABLE humeurs');
        $this->addSql('DROP TABLE patients');
        $this->addSql('DROP TABLE psychologue');
        $this->addSql('DROP TABLE rendezvous');
        $this->addSql('DROP TABLE reponses');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE ressource');
        $this->addSql('DROP TABLE sante_bien_etre');
        $this->addSql('DROP TABLE sleep_tracking');
        $this->addSql('DROP TABLE tests');
        $this->addSql('DROP TABLE users');
        $this->addSql('ALTER TABLE forum_posts DROP FOREIGN KEY `forum_posts_ibfk_1`');
        $this->addSql('DROP INDEX user_id ON forum_posts');
        $this->addSql('ALTER TABLE forum_posts DROP user_id, CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE date_creation date_creation DATETIME NOT NULL, CHANGE archive archive TINYINT NOT NULL, CHANGE likes likes INT NOT NULL, CHANGE vues vues INT NOT NULL, CHANGE dislikes dislikes INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admins (id_user INT NOT NULL, actif TINYINT NOT NULL, INDEX fk_user_admin (id_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE alertes_urgence (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, message TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, localisation VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_alerte DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'ENVOYEE\' COLLATE `utf8mb4_general_ci`, INDEX idx_date (date_alerte), INDEX idx_user_id (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE avis (idAvis INT AUTO_INCREMENT NOT NULL, idReservation INT NOT NULL, utilisateur_id INT NOT NULL, note INT NOT NULL, commentaire TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_avis DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, reservation_id INT DEFAULT NULL, sentiment_score DOUBLE PRECISION DEFAULT \'0\', authenticity_score DOUBLE PRECISION DEFAULT \'1\', review_category VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'General\' COLLATE `utf8mb4_general_ci`, is_verified TINYINT DEFAULT 0, UNIQUE INDEX uk_avis_res_user (idReservation, utilisateur_id), PRIMARY KEY (idAvis)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cabinet (idCabinet INT AUTO_INCREMENT NOT NULL, nomcabinet VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, adresse VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, ville VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, telephone INT NOT NULL, email VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, status VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (idCabinet)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, icone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, couleur VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, UNIQUE INDEX nom (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE conseils (id INT AUTO_INCREMENT NOT NULL, type_etat VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, niveau INT DEFAULT NULL, conseil TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, categorie VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE conseils_utilisateurs (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, conseil_id INT DEFAULT NULL, date_attribution DATETIME DEFAULT CURRENT_TIMESTAMP, est_vu TINYINT DEFAULT 0, INDEX conseil_id (conseil_id), INDEX utilisateur_id (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE doctors (id_user INT NOT NULL, specialty VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, experience INT NOT NULL, diplome VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, disponible TINYINT NOT NULL, tarifConsultation DOUBLE PRECISION NOT NULL, actif TINYINT NOT NULL, INDEX fk_user_doctor (id_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, userId INT NOT NULL, ressourceId INT NOT NULL, note INT DEFAULT NULL, commentaire TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, dateEvaluation DATE DEFAULT NULL, INDEX userId (userId), INDEX ressourceId (ressourceId), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE evenement (idEvenement INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date DATE NOT NULL, localisation VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, popularity_score DOUBLE PRECISION DEFAULT \'0\', predicted_attendance INT DEFAULT 0, dynamic_price DOUBLE PRECISION DEFAULT \'50\', base_price DOUBLE PRECISION DEFAULT \'50\', max_capacity INT DEFAULT 100, venue_layout VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'standard\' COLLATE `utf8mb4_general_ci`, PRIMARY KEY (idEvenement)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE favori (id INT AUTO_INCREMENT NOT NULL, userId INT NOT NULL, ressourceId INT NOT NULL, dateAjout DATE DEFAULT NULL, INDEX userId (userId), INDEX ressourceId (ressourceId), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE humeurs (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, niveau INT NOT NULL, notes TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX utilisateur_id (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE patients (id_user INT NOT NULL, blood_type VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, weight DOUBLE PRECISION NOT NULL, height DOUBLE PRECISION NOT NULL, INDEX fk_user_patient (id_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE psychologue (idPsychologue INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prenom VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, specialite VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, diplome VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, experience INT NOT NULL, tarif INT NOT NULL, email VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, telephone INT NOT NULL, idCabinet INT DEFAULT NULL, INDEX idCabinet (idCabinet), PRIMARY KEY (idPsychologue)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE rendezvous (idRdv INT AUTO_INCREMENT NOT NULL, dateRdv DATE NOT NULL, heure TIME NOT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, typeCons VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, idPsychologue INT NOT NULL, telephone_patient VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, email_patient VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, nom_patient VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, prenom_patient VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, rappel_envoye TINYINT DEFAULT 0, date_rappel DATETIME DEFAULT NULL, INDEX idPsychologue (idPsychologue), PRIMARY KEY (idRdv)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reponses (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, user_id INT NOT NULL, auteur VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, contenu TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_reponse DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, likes INT DEFAULT 0, dislikes INT DEFAULT 0, INDEX post_id (post_id), INDEX user_id (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reservation (idReservation INT AUTO_INCREMENT NOT NULL, idEvenement INT NOT NULL, utilisateur_id INT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, telephone VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, nombre_personnes INT DEFAULT 1, date_reservation DATETIME DEFAULT CURRENT_TIMESTAMP, fraud_probability DOUBLE PRECISION DEFAULT \'0\', is_suspicious TINYINT DEFAULT 0, allocated_seats TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, seating_preference VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'auto\' COLLATE `utf8mb4_general_ci`, INDEX idEvenement (idEvenement), INDEX utilisateur_id (utilisateur_id), PRIMARY KEY (idReservation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ressource (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, category VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, content TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, author VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, dateCreation DATE DEFAULT NULL, status VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE sante_bien_etre (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, humeur VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, niveau_stress INT NOT NULL, qualite_sommeil INT NOT NULL, nutrition VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, activite_physique VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, developpement_personnel VARCHAR(500) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, recommandations TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_suivi DATE NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX user_id (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE sleep_tracking (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date_sommeil DATE NOT NULL, heure_coucher TIME NOT NULL, heure_reveil TIME NOT NULL, duree_minutes INT NOT NULL, qualite_sommeil TINYINT NOT NULL, commentaire VARCHAR(1000) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX user_id (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tests (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, type_test VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, score INT NOT NULL, date_test DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX utilisateur_id (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, second_name VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, age INT NOT NULL, gender VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, phone_number INT NOT NULL, birth_date VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, phone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, is_blocked TINYINT DEFAULT 0, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE admins ADD CONSTRAINT `fk_user_admin` FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE alertes_urgence ADD CONSTRAINT `alertes_ibfk_user` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conseils_utilisateurs ADD CONSTRAINT `conseils_utilisateurs_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE conseils_utilisateurs ADD CONSTRAINT `conseils_utilisateurs_ibfk_2` FOREIGN KEY (conseil_id) REFERENCES conseils (id)');
        $this->addSql('ALTER TABLE doctors ADD CONSTRAINT `fk_user_doctor` FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT `evaluation_ibfk_1` FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT `evaluation_ibfk_2` FOREIGN KEY (ressourceId) REFERENCES ressource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favori ADD CONSTRAINT `favori_ibfk_1` FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favori ADD CONSTRAINT `favori_ibfk_2` FOREIGN KEY (ressourceId) REFERENCES ressource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE humeurs ADD CONSTRAINT `humeurs_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE patients ADD CONSTRAINT `fk_user_patient` FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE psychologue ADD CONSTRAINT `psychologue_ibfk_1` FOREIGN KEY (idCabinet) REFERENCES cabinet (idCabinet) ON UPDATE CASCADE');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT `rendezvous_ibfk_1` FOREIGN KEY (idPsychologue) REFERENCES psychologue (idPsychologue) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT `reponses_ibfk_1` FOREIGN KEY (post_id) REFERENCES forum_posts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT `reponses_ibfk_2` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (idEvenement) REFERENCES evenement (idEvenement)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE sante_bien_etre ADD CONSTRAINT `sante_bien_etre_ibfk_1` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sleep_tracking ADD CONSTRAINT `sleep_tracking_ibfk_1` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tests ADD CONSTRAINT `tests_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7AA15D77E');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE forum_posts ADD user_id INT DEFAULT NULL, CHANGE contenu contenu TEXT NOT NULL, CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE archive archive TINYINT DEFAULT 0, CHANGE likes likes INT DEFAULT 0, CHANGE vues vues INT DEFAULT 0, CHANGE dislikes dislikes INT DEFAULT 0');
        $this->addSql('ALTER TABLE forum_posts ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX user_id ON forum_posts (user_id)');
    }
}
