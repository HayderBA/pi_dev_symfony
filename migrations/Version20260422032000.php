<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422032000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Integrate wellness module: conseils, humeurs, sleep tracking, tests, health follow-up and sport programmes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE conseils (
            id INT AUTO_INCREMENT NOT NULL,
            type_etat VARCHAR(50) NOT NULL,
            niveau INT DEFAULT NULL,
            conseil LONGTEXT NOT NULL,
            categorie VARCHAR(50) DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE conseils_utilisateurs (
            conseil_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            INDEX IDX_CONSEIL_ID (conseil_id),
            INDEX IDX_UTILISATEUR_ID (utilisateur_id),
            PRIMARY KEY(conseil_id, utilisateur_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE humeurs (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT DEFAULT NULL,
            niveau INT NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            date_creation DATETIME DEFAULT NULL,
            INDEX IDX_HUMEUR_USER (utilisateur_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE sante_bien_etre (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            humeur VARCHAR(50) NOT NULL,
            niveau_stress INT NOT NULL,
            qualite_sommeil INT NOT NULL,
            nutrition VARCHAR(255) DEFAULT NULL,
            activite_physique VARCHAR(255) DEFAULT NULL,
            developpement_personnel VARCHAR(500) DEFAULT NULL,
            recommandations LONGTEXT DEFAULT NULL,
            date_suivi DATE NOT NULL,
            date_creation DATETIME NOT NULL,
            INDEX IDX_SANTE_USER (user_id),
            INDEX IDX_SANTE_USER_DATE (user_id, date_suivi),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE sleep_tracking (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            date_sommeil DATE NOT NULL,
            heure_coucher TIME NOT NULL,
            heure_reveil TIME NOT NULL,
            duree_minutes INT NOT NULL,
            qualite_sommeil INT NOT NULL,
            commentaire VARCHAR(1000) DEFAULT NULL,
            date_creation DATETIME NOT NULL,
            INDEX IDX_SLEEP_USER (user_id),
            INDEX IDX_SLEEP_DATE (date_sommeil),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE tests (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT NOT NULL,
            type_test VARCHAR(50) NOT NULL,
            score INT NOT NULL,
            date_test DATETIME DEFAULT NULL,
            INDEX IDX_TEST_USER (utilisateur_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE programme_sportif (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            age INT NOT NULL,
            genre VARCHAR(10) NOT NULL,
            taille_cm DOUBLE PRECISION NOT NULL,
            poids_kg DOUBLE PRECISION NOT NULL,
            niveau_stress INT NOT NULL,
            qualite_sommeil INT NOT NULL,
            duree_sommeil_heures DOUBLE PRECISION NOT NULL,
            niveau_activite VARCHAR(20) NOT NULL,
            objectif VARCHAR(30) NOT NULL,
            activite_cible VARCHAR(80) NOT NULL,
            imc DOUBLE PRECISION DEFAULT NULL,
            categorie_imc VARCHAR(30) DEFAULT NULL,
            source_imc VARCHAR(50) DEFAULT NULL,
            besoin_calorique INT DEFAULT NULL,
            calories_activite INT DEFAULT NULL,
            source_calories VARCHAR(50) DEFAULT NULL,
            intensite VARCHAR(30) DEFAULT NULL,
            type_programme VARCHAR(60) DEFAULT NULL,
            resume LONGTEXT DEFAULT NULL,
            seances JSON NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            INDEX IDX_PROGRAMME_USER (user_id),
            INDEX IDX_PROGRAMME_USER_CREATED (user_id, created_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql('ALTER TABLE conseils_utilisateurs ADD CONSTRAINT FK_CONSEIL_USER_CONSEIL FOREIGN KEY (conseil_id) REFERENCES conseils (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conseils_utilisateurs ADD CONSTRAINT FK_CONSEIL_USER_USER FOREIGN KEY (utilisateur_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE humeurs ADD CONSTRAINT FK_HUMEUR_USER FOREIGN KEY (utilisateur_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sante_bien_etre ADD CONSTRAINT FK_SANTE_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sleep_tracking ADD CONSTRAINT FK_SLEEP_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tests ADD CONSTRAINT FK_TEST_USER FOREIGN KEY (utilisateur_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE programme_sportif ADD CONSTRAINT FK_PROGRAMME_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql("INSERT INTO conseils (type_etat, niveau, conseil, categorie) VALUES
            ('stress', 8, 'Essayez une marche calme, une respiration profonde et une reduction des ecrans avant le coucher.', 'sante mentale'),
            ('fatigue', 5, 'Hydratez-vous, allegez la journee et priorisez une heure de sommeil stable ce soir.', 'sommeil'),
            ('motivation', 3, 'Gardez une routine simple avec un objectif realiste sur la journee.', 'developpement personnel')");

        $this->addSql("INSERT INTO humeurs (utilisateur_id, niveau, notes, date_creation) VALUES
            (1, 4, 'Journee plutot stable avec une bonne energie.', NOW()),
            (2, 3, 'Besoin de repos et de recentrage.', NOW())");

        $this->addSql("INSERT INTO sante_bien_etre (user_id, humeur, niveau_stress, qualite_sommeil, nutrition, activite_physique, developpement_personnel, recommandations, date_suivi, date_creation) VALUES
            (1, 'Bien', 5, 7, 'Equilibree', 'Marche', 'Lecture', 'Continuez vos habitudes actuelles et gardez un rythme de vie regulier.', CURDATE(), NOW()),
            (2, 'Stresse', 8, 4, 'Irreguliere', 'Yoga doux', 'Meditation', 'Votre stress, votre sommeil et votre humeur montrent une surcharge. Levez le pied aujourd hui et prevoyez une routine du soir tres calme.', CURDATE(), NOW())");

        $this->addSql("INSERT INTO sleep_tracking (user_id, date_sommeil, heure_coucher, heure_reveil, duree_minutes, qualite_sommeil, commentaire, date_creation) VALUES
            (1, CURDATE(), '22:30:00', '06:30:00', 480, 7, 'Sommeil correct avec reveil plutot calme.', NOW()),
            (2, CURDATE(), '00:15:00', '06:00:00', 345, 4, 'Sommeil coupe avec fatigue au reveil.', NOW())");

        $this->addSql("INSERT INTO tests (utilisateur_id, type_test, score, date_test) VALUES
            (1, 'stress', 4, NOW()),
            (2, 'sommeil', 7, NOW())");

        $this->addSql("INSERT INTO programme_sportif (
            user_id, age, genre, taille_cm, poids_kg, niveau_stress, qualite_sommeil, duree_sommeil_heures, niveau_activite, objectif, activite_cible, imc, categorie_imc, source_imc, besoin_calorique, calories_activite, source_calories, intensite, type_programme, resume, seances, created_at
        ) VALUES (
            1, 28, 'male', 180, 82, 5, 7, 7.5, 'moderate', 'maintien', 'cycling', 25.3, 'Surpoids', 'Calcul local', 2700, 260, 'Calcul local', 'Moderee', 'Equilibre global', 'Programme genere localement pour maintenir la forme avec une progression douce et reguliere.', '[{\"jour\":\"Lundi\",\"titre\":\"Cardio modere\",\"duree\":35,\"details\":\"Seance continue sur activite preferee.\"},{\"jour\":\"Mercredi\",\"titre\":\"Mobilite\",\"duree\":20,\"details\":\"Souplesse active et respiration.\"},{\"jour\":\"Samedi\",\"titre\":\"Sortie plaisir\",\"duree\":40,\"details\":\"Activite exterieure en aisance.\"}]', NOW()
        )");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conseils_utilisateurs DROP FOREIGN KEY FK_CONSEIL_USER_CONSEIL');
        $this->addSql('ALTER TABLE conseils_utilisateurs DROP FOREIGN KEY FK_CONSEIL_USER_USER');
        $this->addSql('ALTER TABLE humeurs DROP FOREIGN KEY FK_HUMEUR_USER');
        $this->addSql('ALTER TABLE sante_bien_etre DROP FOREIGN KEY FK_SANTE_USER');
        $this->addSql('ALTER TABLE sleep_tracking DROP FOREIGN KEY FK_SLEEP_USER');
        $this->addSql('ALTER TABLE tests DROP FOREIGN KEY FK_TEST_USER');
        $this->addSql('ALTER TABLE programme_sportif DROP FOREIGN KEY FK_PROGRAMME_USER');
        $this->addSql('DROP TABLE conseils_utilisateurs');
        $this->addSql('DROP TABLE conseils');
        $this->addSql('DROP TABLE humeurs');
        $this->addSql('DROP TABLE sante_bien_etre');
        $this->addSql('DROP TABLE sleep_tracking');
        $this->addSql('DROP TABLE tests');
        $this->addSql('DROP TABLE programme_sportif');
    }
}
