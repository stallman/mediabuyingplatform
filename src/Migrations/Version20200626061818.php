<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200626061818 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE algorithms_aggregated_statistics (id INT AUTO_INCREMENT NOT NULL, mediabuyer_id INT NOT NULL, algorithm_id INT NOT NULL, ctr NUMERIC(7, 4) NOT NULL, conversion INT DEFAULT 0 NOT NULL, approve_conversion INT DEFAULT 0 NOT NULL, e_cpm NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, epc NUMERIC(8, 4) DEFAULT \'0\' NOT NULL, cr NUMERIC(8, 4) DEFAULT \'0\' NOT NULL, roi NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, INDEX IDX_25F24E0A79EA3016 (mediabuyer_id), INDEX IDX_25F24E0ABBEB6CF7 (algorithm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE algorithms_aggregated_statistics ADD CONSTRAINT FK_25F24E0A79EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE algorithms_aggregated_statistics ADD CONSTRAINT FK_25F24E0ABBEB6CF7 FOREIGN KEY (algorithm_id) REFERENCES algorithms (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE algorithms_aggregated_statistics');
    }
}
