<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove legacy full_name column after split';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP COLUMN IF EXISTS full_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN IF NOT EXISTS full_name VARCHAR(180) NOT NULL DEFAULT ""');
    }
}
