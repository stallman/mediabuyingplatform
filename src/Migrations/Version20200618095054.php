<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200618095054 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE news_click (id INT AUTO_INCREMENT NOT NULL, buyer_id INT NOT NULL, source_id INT DEFAULT NULL, country_code VARCHAR(2) DEFAULT NULL, traffic_type ENUM(\'desctop\', \'tablet\', \'mobile\'), page_type ENUM(\'top\', \'category\'), user_ip VARCHAR(39) NOT NULL, uuid VARCHAR(36) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_C049457E6C755722 (buyer_id), INDEX IDX_C049457E953C1C61 (source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news_click ADD CONSTRAINT FK_C049457E6C755722 FOREIGN KEY (buyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE news_click ADD CONSTRAINT FK_C049457E953C1C61 FOREIGN KEY (source_id) REFERENCES sources (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE news_click');
    }
}
