<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200715062203 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE algorithms_aggregated_statistics CHANGE e_cpm e_cpm DOUBLE PRECISION NOT NULL, CHANGE epc epc DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE algorithms_aggregated_statistics CHANGE e_cpm e_cpm NUMERIC(7, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE epc epc NUMERIC(8, 4) DEFAULT \'0.0000\' NOT NULL');
    }
}
