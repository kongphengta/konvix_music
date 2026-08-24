<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260609160635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY `FK_33EDEEA1C365A331`');
        $this->addSql('DROP INDEX IDX_33EDEEA1C365A331 ON song');
        $this->addSql('ALTER TABLE song DROP songs_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song ADD songs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT `FK_33EDEEA1C365A331` FOREIGN KEY (songs_id) REFERENCES album (id)');
        $this->addSql('CREATE INDEX IDX_33EDEEA1C365A331 ON song (songs_id)');
    }
}
