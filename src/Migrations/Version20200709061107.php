<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200709061107 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE statistic_news CHANGE conversion conversion INT DEFAULT 0 NOT NULL, CHANGE approve_conversion approve_conversion INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE statistic_teasers CHANGE conversion conversion INT DEFAULT 0 NOT NULL, CHANGE approve_conversion approve_conversion INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE statistic_news CHANGE conversion conversion NUMERIC(7, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE approve_conversion approve_conversion NUMERIC(7, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE statistic_teasers CHANGE conversion conversion NUMERIC(7, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE approve_conversion approve_conversion NUMERIC(7, 4) DEFAULT \'0.0000\' NOT NULL');
    }
}
