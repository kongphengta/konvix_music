<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608191134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song ADD category_id INT NOT NULL, DROP category');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_33EDEEA112469DE2 ON song (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA112469DE2');
        $this->addSql('DROP INDEX IDX_33EDEEA112469DE2 ON song');
        $this->addSql('ALTER TABLE song ADD category VARCHAR(100) DEFAULT NULL, DROP category_id');
    }
}
