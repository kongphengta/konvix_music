<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260909110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reset token columns to user table for password reset workflow';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD reset_token VARCHAR(255) DEFAULT NULL, ADD reset_token_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP reset_token, DROP reset_token_expires_at');
    }
}
