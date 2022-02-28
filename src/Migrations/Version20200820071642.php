<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200820071642 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX map_idx ON conversions (mediabuyer_id, uuid)');
        $this->addSql('CREATE INDEX map_idx ON teasers_click (buyer_id, uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2BA07E9C7FD9892B ON conversion_status (label_en)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX map_idx ON conversions');
        $this->addSql('DROP INDEX map_idx ON teasers_click');
        $this->addSql('DROP INDEX UNIQ_2BA07E9C7FD9892B ON conversion_status');
    }
}
