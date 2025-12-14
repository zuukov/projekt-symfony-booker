<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251214184200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create all database tables for the booking system';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE business (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, business_name VARCHAR(255) NOT NULL, logo_url VARCHAR(500), description LONGTEXT, address VARCHAR(500) NOT NULL, city VARCHAR(255) NOT NULL, postal_code VARCHAR(20) NOT NULL, formal_business_name VARCHAR(255) NOT NULL, phone VARCHAR(20) NOT NULL, secondary_phone VARCHAR(20), email VARCHAR(255) NOT NULL, instagram_url VARCHAR(500), facebook_url VARCHAR(500), website_url VARCHAR(500), PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D93BA0A7E3C61F9 ON business (owner_id)');
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, business_id INT NOT NULL, service_id INT NOT NULL, staff_id INT NOT NULL, user_id INT NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, price_at_booking NUMERIC(10, 2) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA89DBBDD ON booking (business_id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDEED5CA9E6 ON booking (service_id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA04A0361 ON booking (staff_id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA76ED395 ON booking (user_id)');
        $this->addSql('CREATE TABLE business_working_hours (id INT AUTO_INCREMENT NOT NULL, business_id INT NOT NULL, weekday SMALLINT NOT NULL, opens_at TIME NOT NULL, closes_at TIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5B2B182FA89DBBDD ON business_working_hours (business_id)');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, business_id INT NOT NULL, user_id INT NOT NULL, rating SMALLINT NOT NULL, comment LONGTEXT, created_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_794381C6A89DBBDD ON review (business_id)');
        $this->addSql('CREATE INDEX IDX_794381C6A76ED395 ON review (user_id)');
        $this->addSql('CREATE TABLE service_category (id INT AUTO_INCREMENT NOT NULL, category_full_name VARCHAR(255) NOT NULL, category_friendly_name VARCHAR(255) NOT NULL, featured_image VARCHAR(255), PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, business_id INT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT, featured_image VARCHAR(500), duration_minutes INT NOT NULL, price NUMERIC(10, 2) NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E19D9AD2A89DBBDD ON service (business_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD212469DE2 ON service (category_id)');
        $this->addSql('CREATE TABLE staff (id INT AUTO_INCREMENT NOT NULL, business_id INT NOT NULL, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, avatar_image VARCHAR(500), PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_426EF392A89DBBDD ON staff (business_id)');
        $this->addSql('CREATE TABLE staff_service (id INT AUTO_INCREMENT NOT NULL, staff_id INT NOT NULL, service_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6A61B26AD4D57CD ON staff_service (staff_id)');
        $this->addSql('CREATE INDEX IDX_6A61B26AED5CA9E6 ON staff_service (service_id)');
        $this->addSql('CREATE TABLE staff_time_off (id INT AUTO_INCREMENT NOT NULL, staff_id INT NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, reason VARCHAR(500), PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D3B3B6D4D57CD ON staff_time_off (staff_id)');
        $this->addSql('CREATE TABLE staff_working_hours (id INT AUTO_INCREMENT NOT NULL, staff_id INT NOT NULL, weekday SMALLINT NOT NULL, starts_at TIME NOT NULL, ends_at TIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8B3C7A4D4D57CD ON staff_working_hours (staff_id)');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(20), name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93BA0AE7927C74 ON user (email)');
        $this->addSql('ALTER TABLE business ADD CONSTRAINT FK_8D93BA0A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA89DBBDD FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA04A0361 FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE business_working_hours ADD CONSTRAINT FK_5B2B182FA89DBBDD FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A89DBBDD FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2A89DBBDD FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD212469DE2 FOREIGN KEY (category_id) REFERENCES service_category (id)');
        $this->addSql('ALTER TABLE staff ADD CONSTRAINT FK_426EF392A89DBBDD FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE staff_service ADD CONSTRAINT FK_6A61B26AD4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE staff_service ADD CONSTRAINT FK_6A61B26AED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE staff_time_off ADD CONSTRAINT FK_8D3B3B6D4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE staff_working_hours ADD CONSTRAINT FK_8B3C7A4D4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE business DROP FOREIGN KEY FK_8D93BA0A7E3C61F9');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA89DBBDD');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEED5CA9E6');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA04A0361');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA76ED395');
        $this->addSql('ALTER TABLE business_working_hours DROP FOREIGN KEY FK_5B2B182FA89DBBDD');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A89DBBDD');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2A89DBBDD');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD212469DE2');
        $this->addSql('ALTER TABLE staff DROP FOREIGN KEY FK_426EF392A89DBBDD');
        $this->addSql('ALTER TABLE staff_service DROP FOREIGN KEY FK_6A61B26AD4D57CD');
        $this->addSql('ALTER TABLE staff_service DROP FOREIGN KEY FK_6A61B26AED5CA9E6');
        $this->addSql('ALTER TABLE staff_time_off DROP FOREIGN KEY FK_8D3B3B6D4D57CD');
        $this->addSql('ALTER TABLE staff_working_hours DROP FOREIGN KEY FK_8B3C7A4D4D57CD');
        $this->addSql('DROP TABLE business');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE business_working_hours');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE service_category');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE staff');
        $this->addSql('DROP TABLE staff_service');
        $this->addSql('DROP TABLE staff_time_off');
        $this->addSql('DROP TABLE staff_working_hours');
        $this->addSql('DROP TABLE user');
    }
}
