<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260609152801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE album (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, cover VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE category DROP description');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C1989D9B62 ON category (slug)');
        $this->addSql('ALTER TABLE song ADD album_id INT DEFAULT NULL, ADD songs_id INT DEFAULT NULL, DROP album, DROP description, DROP created_at, CHANGE filename filename VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA11137ABCF FOREIGN KEY (album_id) REFERENCES album (id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1C365A331 FOREIGN KEY (songs_id) REFERENCES album (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_33EDEEA1989D9B62 ON song (slug)');
        $this->addSql('CREATE INDEX IDX_33EDEEA11137ABCF ON song (album_id)');
        $this->addSql('CREATE INDEX IDX_33EDEEA1C365A331 ON song (songs_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP INDEX UNIQ_64C19C1989D9B62 ON category');
        $this->addSql('ALTER TABLE category ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA11137ABCF');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1C365A331');
        $this->addSql('DROP INDEX UNIQ_33EDEEA1989D9B62 ON song');
        $this->addSql('DROP INDEX IDX_33EDEEA11137ABCF ON song');
        $this->addSql('DROP INDEX IDX_33EDEEA1C365A331 ON song');
        $this->addSql('ALTER TABLE song ADD album VARCHAR(150) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD created_at DATETIME NOT NULL, DROP album_id, DROP songs_id, CHANGE filename filename VARCHAR(255) NOT NULL');
    }
}
