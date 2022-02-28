<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200824132245 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE INDEX impressions_idx ON top_teasers (impressions)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX impressions_idx ON top_teasers');
    }
}
