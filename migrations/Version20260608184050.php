<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608184050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY `FK_64C19C1C365A331`');
        $this->addSql('DROP INDEX IDX_64C19C1C365A331 ON category');
        $this->addSql('ALTER TABLE category DROP songs_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD songs_id INT NOT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT `FK_64C19C1C365A331` FOREIGN KEY (songs_id) REFERENCES song (id)');
        $this->addSql('CREATE INDEX IDX_64C19C1C365A331 ON category (songs_id)');
    }
}
