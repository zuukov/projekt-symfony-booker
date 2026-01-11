<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add safety_rules and amenities JSON fields to business table
 */
final class Version20260111183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add safety_rules and amenities JSON fields to business table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE business ADD COLUMN safety_rules TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE business ADD COLUMN amenities TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE business DROP COLUMN safety_rules');
        $this->addSql('ALTER TABLE business DROP COLUMN amenities');
    }
}
