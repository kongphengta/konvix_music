<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260921044408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE track (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, duration VARCHAR(255) NOT NULL, audio_url VARCHAR(255) NOT NULL, is_published TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, artist_id INT DEFAULT NULL, INDEX IDX_D6E3F8A6B7970CF8 (artist_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A6B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE user CHANGE first_name first_name VARCHAR(180) NOT NULL, CHANGE last_name last_name VARCHAR(180) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A6B7970CF8');
        $this->addSql('DROP TABLE track');
        $this->addSql('ALTER TABLE user CHANGE first_name first_name VARCHAR(180) DEFAULT \'\' NOT NULL, CHANGE last_name last_name VARCHAR(180) DEFAULT \'\' NOT NULL');
    }
}
