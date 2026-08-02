<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260802171708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account_activation_token (id INT AUTO_INCREMENT NOT NULL, token_hash VARCHAR(255) NOT NULL, issued_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, consumed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_D7523EBF9D86650F (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE account_activation_token ADD CONSTRAINT FK_D7523EBF9D86650F FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account_activation_token DROP FOREIGN KEY FK_D7523EBF9D86650F');
        $this->addSql('DROP TABLE account_activation_token');
    }
}
