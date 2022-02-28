<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200618085925 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE statistic_promo_block_teasers (id INT AUTO_INCREMENT NOT NULL, teaser_id INT NOT NULL, mediabuyer_id INT NOT NULL, source_id INT DEFAULT NULL, news_id INT DEFAULT NULL, country_code VARCHAR(2) DEFAULT NULL, traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), page_type ENUM(\'top\', \'short\', \'full\'), created_at DATETIME NOT NULL, INDEX IDX_EA8000F87ADE9C9E (teaser_id), INDEX IDX_EA8000F879EA3016 (mediabuyer_id), INDEX IDX_EA8000F8953C1C61 (source_id), INDEX IDX_EA8000F8B5A459A0 (news_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers ADD CONSTRAINT FK_EA8000F87ADE9C9E FOREIGN KEY (teaser_id) REFERENCES teasers (id)');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers ADD CONSTRAINT FK_EA8000F879EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers ADD CONSTRAINT FK_EA8000F8953C1C61 FOREIGN KEY (source_id) REFERENCES sources (id)');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers ADD CONSTRAINT FK_EA8000F8B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE statistic_promo_block_teasers');
    }
}
