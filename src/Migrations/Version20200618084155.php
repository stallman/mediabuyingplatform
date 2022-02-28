<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200618084155 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE statistic_promo_block_news (id INT AUTO_INCREMENT NOT NULL, news_id INT NOT NULL, mediabuyer_id INT NOT NULL, source_id INT DEFAULT NULL, country_code VARCHAR(2) DEFAULT NULL, traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), page_type ENUM(\'top\', \'category\'), created_at DATETIME NOT NULL, INDEX IDX_2AAD721EB5A459A0 (news_id), INDEX IDX_2AAD721E79EA3016 (mediabuyer_id), INDEX IDX_2AAD721E953C1C61 (source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE statistic_promo_block_news ADD CONSTRAINT FK_2AAD721EB5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE statistic_promo_block_news ADD CONSTRAINT FK_2AAD721E79EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE statistic_promo_block_news ADD CONSTRAINT FK_2AAD721E953C1C61 FOREIGN KEY (source_id) REFERENCES sources (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE statistic_promo_block_news');
    }
}
