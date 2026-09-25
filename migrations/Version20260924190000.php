<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add scheduled publication date to tracks';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE track ADD published_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE track DROP published_at');
    }
}
