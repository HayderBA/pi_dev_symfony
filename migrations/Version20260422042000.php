<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422042000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reset password token storage for user management';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE reset_password_token (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_RESET_PASSWORD_TOKEN (token),
            INDEX IDX_RESET_PASSWORD_USER (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql('ALTER TABLE reset_password_token ADD CONSTRAINT FK_RESET_PASSWORD_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reset_password_token DROP FOREIGN KEY FK_RESET_PASSWORD_USER');
        $this->addSql('DROP TABLE reset_password_token');
    }
}
