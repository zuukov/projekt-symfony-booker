<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260111004829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cards CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE card_number card_number INT NOT NULL, CHANGE valid_until valid_until VARCHAR(255) NOT NULL, CHANGE cvc_code cvc_code INT NOT NULL, CHANGE country country VARCHAR(255) NOT NULL, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cards MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE cards CHANGE id id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE card_number card_number INT DEFAULT NULL, CHANGE valid_until valid_until VARCHAR(5) DEFAULT NULL, CHANGE cvc_code cvc_code INT DEFAULT NULL, CHANGE country country VARCHAR(255) DEFAULT NULL, DROP PRIMARY KEY');
    }
}
