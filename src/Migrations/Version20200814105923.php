<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200814105923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversions DROP INDEX IDX_6A02DBA53BD4522D, ADD UNIQUE INDEX UNIQ_6A02DBA53BD4522D (teaser_click_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversions DROP INDEX UNIQ_6A02DBA53BD4522D, ADD INDEX IDX_6A02DBA53BD4522D (teaser_click_id)');
    }
}
