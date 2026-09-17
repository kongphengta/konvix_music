<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add verification fields to user table for registration flow';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN IF NOT EXISTS is_verified TINYINT NOT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN IF NOT EXISTS email_verification_code VARCHAR(6) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP COLUMN IF EXISTS is_verified');
        $this->addSql('ALTER TABLE user DROP COLUMN IF EXISTS email_verification_code');
    }
}
