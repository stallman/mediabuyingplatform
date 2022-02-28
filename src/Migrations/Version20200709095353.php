<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200709095353 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE top_news (id INT AUTO_INCREMENT NOT NULL, news_id INT NOT NULL, mediabuyer_id INT NOT NULL, geo_code VARCHAR(2) NOT NULL, traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), e_cpm NUMERIC(8, 4) NOT NULL, INDEX IDX_BD306F98B5A459A0 (news_id), INDEX IDX_BD306F9879EA3016 (mediabuyer_id), UNIQUE INDEX unique_map_idx (news_id, mediabuyer_id, geo_code, traffic_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE top_teasers (id INT AUTO_INCREMENT NOT NULL, teaser_id INT NOT NULL, mediabuyer_id INT NOT NULL, geo_code VARCHAR(2) NOT NULL, traffic_type ENUM(\'desktop\', \'tablet\', \'mobile\'), e_cpm NUMERIC(8, 4) NOT NULL, INDEX IDX_7EEAE4B87ADE9C9E (teaser_id), INDEX IDX_7EEAE4B879EA3016 (mediabuyer_id), UNIQUE INDEX unique_map_idx (teaser_id, mediabuyer_id, geo_code, traffic_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE top_news ADD CONSTRAINT FK_BD306F98B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE top_news ADD CONSTRAINT FK_BD306F9879EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE top_teasers ADD CONSTRAINT FK_7EEAE4B87ADE9C9E FOREIGN KEY (teaser_id) REFERENCES teasers (id)');
        $this->addSql('ALTER TABLE top_teasers ADD CONSTRAINT FK_7EEAE4B879EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE top_news');
        $this->addSql('DROP TABLE top_teasers');
    }
}
