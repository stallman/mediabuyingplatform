<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200617091813 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE visits (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', source_id INT DEFAULT NULL, mediabuyer_id INT DEFAULT NULL, domain_id INT DEFAULT NULL, country_code VARCHAR(2) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, utm_content VARCHAR(255) DEFAULT NULL, utm_campaign VARCHAR(255) DEFAULT NULL, ip VARCHAR(39) NOT NULL, traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), os VARCHAR(255) DEFAULT NULL, os_with_version VARCHAR(255) DEFAULT NULL, browser VARCHAR(255) DEFAULT NULL, browser_with_version VARCHAR(255) DEFAULT NULL, mobile_brand VARCHAR(255) DEFAULT NULL, mobile_model VARCHAR(255) DEFAULT NULL, mobile_operator VARCHAR(255) DEFAULT NULL, screen_size VARCHAR(255) DEFAULT NULL, subid1 VARCHAR(255) DEFAULT NULL, subid2 VARCHAR(255) DEFAULT NULL, subid3 VARCHAR(255) DEFAULT NULL, subid4 VARCHAR(255) DEFAULT NULL, subid5 VARCHAR(255) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_444839EA953C1C61 (source_id), INDEX IDX_444839EA79EA3016 (mediabuyer_id), INDEX IDX_444839EA115F0EE5 (domain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE visits ADD CONSTRAINT FK_444839EA953C1C61 FOREIGN KEY (source_id) REFERENCES sources (id)');
        $this->addSql('ALTER TABLE visits ADD CONSTRAINT FK_444839EA79EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE visits ADD CONSTRAINT FK_444839EA115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain_parking (id)'); }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE visits');
    }
}
