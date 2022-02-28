<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200727110601 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE conversions ADD news_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA5B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('CREATE INDEX IDX_6A02DBA5B5A459A0 ON conversions (news_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE conversions DROP FOREIGN KEY FK_6A02DBA5B5A459A0');
        $this->addSql('DROP INDEX IDX_6A02DBA5B5A459A0 ON conversions');
        $this->addSql('ALTER TABLE conversions DROP news_id');
    }
}
