<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Split full name into first and last name fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN IF NOT EXISTS first_name VARCHAR(180) NOT NULL DEFAULT ""');
        $this->addSql('ALTER TABLE user ADD COLUMN IF NOT EXISTS last_name VARCHAR(180) NOT NULL DEFAULT ""');
        $this->addSql('UPDATE user SET first_name = TRIM(SUBSTRING_INDEX(full_name, " ", 1)), last_name = TRIM(SUBSTRING(full_name, CHAR_LENGTH(SUBSTRING_INDEX(full_name, " ", 1)) + 2)) WHERE full_name IS NOT NULL AND full_name <> "" AND (first_name = "" OR last_name = "")');
        $this->addSql('ALTER TABLE user DROP COLUMN IF EXISTS full_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN IF NOT EXISTS full_name VARCHAR(180) NOT NULL DEFAULT ""');
        $this->addSql('UPDATE user SET full_name = TRIM(CONCAT(first_name, " ", last_name)) WHERE first_name IS NOT NULL OR last_name IS NOT NULL');
        $this->addSql('ALTER TABLE user DROP COLUMN IF EXISTS first_name');
        $this->addSql('ALTER TABLE user DROP COLUMN IF EXISTS last_name');
    }
}
