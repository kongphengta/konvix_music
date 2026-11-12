<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261005073704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist_profile (id INT AUTO_INCREMENT NOT NULL, artist_name VARCHAR(180) NOT NULL, slug VARCHAR(180) NOT NULL, bio LONGTEXT DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, cover_image VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_3618F438989D9B62 (slug), UNIQUE INDEX UNIQ_3618F438A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_verification (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, expires_at DATETIME NOT NULL, is_used TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_DA3DB909A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE artist_profile ADD CONSTRAINT FK_3618F438A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_verification ADD CONSTRAINT FK_DA3DB909A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist_profile DROP FOREIGN KEY FK_3618F438A76ED395');
        $this->addSql('ALTER TABLE user_verification DROP FOREIGN KEY FK_DA3DB909A76ED395');
        $this->addSql('DROP TABLE artist_profile');
        $this->addSql('DROP TABLE user_verification');
    }
}
