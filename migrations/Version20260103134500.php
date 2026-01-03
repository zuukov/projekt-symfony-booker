<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add aboutMe, experience, and school fields to Staff entity
 */
final class Version20260103134500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add aboutMe, experience, and school fields to Staff entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE staff ADD about_me LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE staff ADD experience LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE staff ADD school VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE staff DROP COLUMN about_me');
        $this->addSql('ALTER TABLE staff DROP COLUMN experience');
        $this->addSql('ALTER TABLE staff DROP COLUMN school');
    }
}
