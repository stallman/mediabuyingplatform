<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200820085346 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE INDEX entity_id_entity_fqn ON image (entity_id, entity_fqn)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX entity_id_entity_fqn ON image');
    }
}
