<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201006101843 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE algorithms_aggregated_statistics CHANGE ctr ctr NUMERIC(9, 4) NOT NULL');
        $this->addSql('ALTER TABLE designs_aggregated_statistics CHANGE ctr ctr NUMERIC(9, 4) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE algorithms_aggregated_statistics CHANGE ctr ctr NUMERIC(7, 4) NOT NULL');
        $this->addSql('ALTER TABLE designs_aggregated_statistics CHANGE ctr ctr NUMERIC(7, 4) NOT NULL');
    }
}
