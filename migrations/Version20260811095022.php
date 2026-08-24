<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260811095022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist_profile (id INT AUTO_INCREMENT NOT NULL, stage_name VARCHAR(150) NOT NULL, slug VARCHAR(150) NOT NULL, bio LONGTEXT DEFAULT NULL, genre VARCHAR(100) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, is_approved TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_3618F4389CF67193 (stage_name), UNIQUE INDEX UNIQ_3618F438989D9B62 (slug), UNIQUE INDEX UNIQ_3618F438A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE artist_profile ADD CONSTRAINT FK_3618F438A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE song ADD artist_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist_profile (id)');
        $this->addSql('CREATE INDEX IDX_33EDEEA1B7970CF8 ON song (artist_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist_profile DROP FOREIGN KEY FK_3618F438A76ED395');
        $this->addSql('DROP TABLE artist_profile');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1B7970CF8');
        $this->addSql('DROP INDEX IDX_33EDEEA1B7970CF8 ON song');
        $this->addSql('ALTER TABLE song DROP artist_id');
    }
}
