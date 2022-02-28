<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200804064929 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE other_filters_data (id INT AUTO_INCREMENT NOT NULL, mediabuyer_id INT NOT NULL, type ENUM(\'utm_term\', \'utm_content\', \'utm_campaign\', \'subid1\', \'subid2\', \'subid3\', \'subid4\', \'subid5\'), options VARCHAR(255) NOT NULL, INDEX IDX_ADDF456079EA3016 (mediabuyer_id), INDEX map_idx (mediabuyer_id, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE other_filters_data ADD CONSTRAINT FK_ADDF456079EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE other_filters_data');
    }
}
