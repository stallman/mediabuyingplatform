<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200707100359 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news_click CHANGE traffic_type traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), CHANGE page_type page_type ENUM(\'top\', \'category\')');
        $this->addSql('ALTER TABLE statistic_promo_block_news CHANGE traffic_type traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), CHANGE page_type page_type ENUM(\'top\', \'category\')');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers CHANGE traffic_type traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), CHANGE page_type page_type ENUM(\'top\', \'short\', \'full\')');
        $this->addSql('ALTER TABLE teasers_click CHANGE traffic_type traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), CHANGE page_type page_type ENUM(\'full\', \'short\', \'top\')');
        $this->addSql('ALTER TABLE visits CHANGE traffic_type traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\')');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news_click CHANGE traffic_type traffic_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE page_type page_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE statistic_promo_block_news CHANGE traffic_type traffic_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE page_type page_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers CHANGE traffic_type traffic_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE page_type page_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE teasers_click CHANGE traffic_type traffic_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE page_type page_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE visits CHANGE traffic_type traffic_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
