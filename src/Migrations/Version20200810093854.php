<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200810093854 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversions ADD teaser_click_id INT NOT NULL, DROP click_id');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA53BD4522D FOREIGN KEY (teaser_click_id) REFERENCES teasers_click (id)');
        $this->addSql('CREATE INDEX IDX_6A02DBA53BD4522D ON conversions (teaser_click_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversions DROP FOREIGN KEY FK_6A02DBA53BD4522D');
        $this->addSql('DROP INDEX IDX_6A02DBA53BD4522D ON conversions');
        $this->addSql('ALTER TABLE conversions ADD click_id BIGINT NOT NULL, DROP teaser_click_id');
    }
}
