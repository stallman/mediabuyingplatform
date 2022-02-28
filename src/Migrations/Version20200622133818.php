<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200622133818 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE statistic_promo_block_news ADD algorithm_id INT DEFAULT NULL, ADD design_id INT DEFAULT NULL, CHANGE traffic_type traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), CHANGE page_type page_type ENUM(\'top\', \'category\')');
        $this->addSql('ALTER TABLE statistic_promo_block_news ADD CONSTRAINT FK_2AAD721EBBEB6CF7 FOREIGN KEY (algorithm_id) REFERENCES algorithms (id)');
        $this->addSql('ALTER TABLE statistic_promo_block_news ADD CONSTRAINT FK_2AAD721EE41DC9B2 FOREIGN KEY (design_id) REFERENCES designs (id)');
        $this->addSql('CREATE INDEX IDX_2AAD721EBBEB6CF7 ON statistic_promo_block_news (algorithm_id)');
        $this->addSql('CREATE INDEX IDX_2AAD721EE41DC9B2 ON statistic_promo_block_news (design_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE statistic_promo_block_news DROP FOREIGN KEY FK_2AAD721EBBEB6CF7');
        $this->addSql('ALTER TABLE statistic_promo_block_news DROP FOREIGN KEY FK_2AAD721EE41DC9B2');
        $this->addSql('DROP INDEX IDX_2AAD721EBBEB6CF7 ON statistic_promo_block_news');
        $this->addSql('DROP INDEX IDX_2AAD721EE41DC9B2 ON statistic_promo_block_news');
        $this->addSql('ALTER TABLE statistic_promo_block_news DROP algorithm_id, DROP design_id, CHANGE traffic_type traffic_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE page_type page_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
