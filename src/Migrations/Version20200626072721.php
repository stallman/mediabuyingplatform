<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200626072721 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE designs_aggregated_statistics (id INT AUTO_INCREMENT NOT NULL, mediabuyer_id INT NOT NULL, design_id INT NOT NULL, ctr NUMERIC(7, 4) NOT NULL, conversion INT DEFAULT 0 NOT NULL, approve_conversion INT DEFAULT 0 NOT NULL, epc NUMERIC(8, 4) DEFAULT \'0\' NOT NULL, cr NUMERIC(8, 4) DEFAULT \'0\' NOT NULL, roi NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, INDEX IDX_5C751CED79EA3016 (mediabuyer_id), INDEX IDX_5C751CEDE41DC9B2 (design_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE designs_aggregated_statistics ADD CONSTRAINT FK_5C751CED79EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE designs_aggregated_statistics ADD CONSTRAINT FK_5C751CEDE41DC9B2 FOREIGN KEY (design_id) REFERENCES designs (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE designs_aggregated_statistics');
    }
}
