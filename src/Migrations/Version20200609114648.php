<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200609114648 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE statistic_news CHANGE inner_ctr inner_ctr NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, CHANGE inner_e_cpm inner_e_cpm NUMERIC(8, 4) DEFAULT \'0\' NOT NULL, CHANGE probiv probiv NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, CHANGE conversion conversion NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, CHANGE approve_conversion approve_conversion NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, CHANGE involvement involvement NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, CHANGE epc epc NUMERIC(8, 4) DEFAULT \'0\' NOT NULL, CHANGE cr cr NUMERIC(8, 4) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE statistic_news CHANGE inner_ctr inner_ctr NUMERIC(4, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE inner_e_cpm inner_e_cpm NUMERIC(4, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE probiv probiv NUMERIC(4, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE conversion conversion NUMERIC(4, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE approve_conversion approve_conversion NUMERIC(4, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE involvement involvement NUMERIC(4, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE epc epc NUMERIC(4, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cr cr NUMERIC(4, 4) DEFAULT \'0.0000\' NOT NULL');
    }
}
