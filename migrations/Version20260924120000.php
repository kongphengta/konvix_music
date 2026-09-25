<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add track metadata fields for music style, language and cover image';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE track MODIFY audio_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE track ADD music_style VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE track ADD language VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE track ADD cover_image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE track DROP music_style');
        $this->addSql('ALTER TABLE track DROP language');
        $this->addSql('ALTER TABLE track DROP cover_image');
    }
}
