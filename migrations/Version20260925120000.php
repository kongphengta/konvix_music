<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20260925120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password reset fields to the user entity.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('user');

        if (!$table->hasColumn('password_reset_token')) {
            $table->addColumn('password_reset_token', Types::STRING, [
                'length' => 255,
                'notnull' => false,
            ]);
        }

        if (!$table->hasColumn('password_reset_expires_at')) {
            $table->addColumn('password_reset_expires_at', Types::DATETIME_IMMUTABLE, [
                'notnull' => false,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('user');

        if ($table->hasColumn('password_reset_expires_at')) {
            $table->dropColumn('password_reset_expires_at');
        }

        if ($table->hasColumn('password_reset_token')) {
            $table->dropColumn('password_reset_token');
        }
    }
}
