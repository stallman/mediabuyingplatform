<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200702121717 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversions ADD design_id INT NOT NULL');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA5E41DC9B2 FOREIGN KEY (design_id) REFERENCES designs (id)');
        $this->addSql('CREATE INDEX IDX_6A02DBA5E41DC9B2 ON conversions (design_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversions DROP FOREIGN KEY FK_6A02DBA5E41DC9B2');
        $this->addSql('DROP INDEX IDX_6A02DBA5E41DC9B2 ON conversions');
        $this->addSql('ALTER TABLE conversions DROP design_id');
    }
}
