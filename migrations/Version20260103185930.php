<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260103185930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY `FK_E00CEDDEA04A0361`');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY `FK_E00CEDDEA89DBBDD`');
        $this->addSql('DROP INDEX idx_e00ceddea89dbbdd ON booking');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA89DB457 ON booking (business_id)');
        $this->addSql('DROP INDEX idx_e00ceddea04a0361 ON booking');
        $this->addSql('CREATE INDEX IDX_E00CEDDED4D57CD ON booking (staff_id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT `FK_E00CEDDEA04A0361` FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT `FK_E00CEDDEA89DBBDD` FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE business DROP FOREIGN KEY `FK_8D93BA0A7E3C61F9`');
        $this->addSql('DROP INDEX idx_8d93ba0a7e3c61f9 ON business');
        $this->addSql('CREATE INDEX IDX_8D36E387E3C61F9 ON business (owner_id)');
        $this->addSql('ALTER TABLE business ADD CONSTRAINT `FK_8D93BA0A7E3C61F9` FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE business_working_hours DROP FOREIGN KEY `FK_5B2B182FA89DBBDD`');
        $this->addSql('DROP INDEX idx_5b2b182fa89dbbdd ON business_working_hours');
        $this->addSql('CREATE INDEX IDX_85071626A89DB457 ON business_working_hours (business_id)');
        $this->addSql('ALTER TABLE business_working_hours ADD CONSTRAINT `FK_5B2B182FA89DBBDD` FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY `FK_794381C6A89DBBDD`');
        $this->addSql('DROP INDEX idx_794381c6a89dbbdd ON review');
        $this->addSql('CREATE INDEX IDX_794381C6A89DB457 ON review (business_id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT `FK_794381C6A89DBBDD` FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY `FK_E19D9AD2A89DBBDD`');
        $this->addSql('DROP INDEX idx_e19d9ad2a89dbbdd ON service');
        $this->addSql('CREATE INDEX IDX_E19D9AD2A89DB457 ON service (business_id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT `FK_E19D9AD2A89DBBDD` FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE staff DROP FOREIGN KEY `FK_426EF392A89DBBDD`');
        $this->addSql('DROP INDEX idx_426ef392a89dbbdd ON staff');
        $this->addSql('CREATE INDEX IDX_426EF392A89DB457 ON staff (business_id)');
        $this->addSql('ALTER TABLE staff ADD CONSTRAINT `FK_426EF392A89DBBDD` FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE staff_service DROP FOREIGN KEY `FK_6A61B26AD4D57CD`');
        $this->addSql('ALTER TABLE staff_service DROP FOREIGN KEY `FK_6A61B26AED5CA9E6`');
        $this->addSql('DROP INDEX idx_6a61b26ad4d57cd ON staff_service');
        $this->addSql('CREATE INDEX IDX_BD2B8D64D4D57CD ON staff_service (staff_id)');
        $this->addSql('DROP INDEX idx_6a61b26aed5ca9e6 ON staff_service');
        $this->addSql('CREATE INDEX IDX_BD2B8D64ED5CA9E6 ON staff_service (service_id)');
        $this->addSql('ALTER TABLE staff_service ADD CONSTRAINT `FK_6A61B26AD4D57CD` FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE staff_service ADD CONSTRAINT `FK_6A61B26AED5CA9E6` FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE staff_time_off DROP FOREIGN KEY `FK_8D3B3B6D4D57CD`');
        $this->addSql('DROP INDEX idx_8d3b3b6d4d57cd ON staff_time_off');
        $this->addSql('CREATE INDEX IDX_D3639687D4D57CD ON staff_time_off (staff_id)');
        $this->addSql('ALTER TABLE staff_time_off ADD CONSTRAINT `FK_8D3B3B6D4D57CD` FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE staff_working_hours DROP FOREIGN KEY `FK_8B3C7A4D4D57CD`');
        $this->addSql('DROP INDEX idx_8b3c7a4d4d57cd ON staff_working_hours');
        $this->addSql('CREATE INDEX IDX_94C0A155D4D57CD ON staff_working_hours (staff_id)');
        $this->addSql('ALTER TABLE staff_working_hours ADD CONSTRAINT `FK_8B3C7A4D4D57CD` FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('DROP INDEX uniq_8d93ba0ae7927c74 ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA89DB457');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDED4D57CD');
        $this->addSql('DROP INDEX idx_e00ceddea89db457 ON booking');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA89DBBDD ON booking (business_id)');
        $this->addSql('DROP INDEX idx_e00cedded4d57cd ON booking');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA04A0361 ON booking (staff_id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA89DB457 FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDED4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE business DROP FOREIGN KEY FK_8D36E387E3C61F9');
        $this->addSql('DROP INDEX idx_8d36e387e3c61f9 ON business');
        $this->addSql('CREATE INDEX IDX_8D93BA0A7E3C61F9 ON business (owner_id)');
        $this->addSql('ALTER TABLE business ADD CONSTRAINT FK_8D36E387E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE business_working_hours DROP FOREIGN KEY FK_85071626A89DB457');
        $this->addSql('DROP INDEX idx_85071626a89db457 ON business_working_hours');
        $this->addSql('CREATE INDEX IDX_5B2B182FA89DBBDD ON business_working_hours (business_id)');
        $this->addSql('ALTER TABLE business_working_hours ADD CONSTRAINT FK_85071626A89DB457 FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A89DB457');
        $this->addSql('DROP INDEX idx_794381c6a89db457 ON review');
        $this->addSql('CREATE INDEX IDX_794381C6A89DBBDD ON review (business_id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A89DB457 FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2A89DB457');
        $this->addSql('DROP INDEX idx_e19d9ad2a89db457 ON service');
        $this->addSql('CREATE INDEX IDX_E19D9AD2A89DBBDD ON service (business_id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2A89DB457 FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE staff DROP FOREIGN KEY FK_426EF392A89DB457');
        $this->addSql('DROP INDEX idx_426ef392a89db457 ON staff');
        $this->addSql('CREATE INDEX IDX_426EF392A89DBBDD ON staff (business_id)');
        $this->addSql('ALTER TABLE staff ADD CONSTRAINT FK_426EF392A89DB457 FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE staff_service DROP FOREIGN KEY FK_BD2B8D64D4D57CD');
        $this->addSql('ALTER TABLE staff_service DROP FOREIGN KEY FK_BD2B8D64ED5CA9E6');
        $this->addSql('DROP INDEX idx_bd2b8d64ed5ca9e6 ON staff_service');
        $this->addSql('CREATE INDEX IDX_6A61B26AED5CA9E6 ON staff_service (service_id)');
        $this->addSql('DROP INDEX idx_bd2b8d64d4d57cd ON staff_service');
        $this->addSql('CREATE INDEX IDX_6A61B26AD4D57CD ON staff_service (staff_id)');
        $this->addSql('ALTER TABLE staff_service ADD CONSTRAINT FK_BD2B8D64D4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE staff_service ADD CONSTRAINT FK_BD2B8D64ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE staff_time_off DROP FOREIGN KEY FK_D3639687D4D57CD');
        $this->addSql('DROP INDEX idx_d3639687d4d57cd ON staff_time_off');
        $this->addSql('CREATE INDEX IDX_8D3B3B6D4D57CD ON staff_time_off (staff_id)');
        $this->addSql('ALTER TABLE staff_time_off ADD CONSTRAINT FK_D3639687D4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE staff_working_hours DROP FOREIGN KEY FK_94C0A155D4D57CD');
        $this->addSql('DROP INDEX idx_94c0a155d4d57cd ON staff_working_hours');
        $this->addSql('CREATE INDEX IDX_8B3C7A4D4D57CD ON staff_working_hours (staff_id)');
        $this->addSql('ALTER TABLE staff_working_hours ADD CONSTRAINT FK_94C0A155D4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74 ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93BA0AE7927C74 ON user (email)');
    }
}
