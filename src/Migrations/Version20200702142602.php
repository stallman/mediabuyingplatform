<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200702142602 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visits ADD news_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE visits ADD CONSTRAINT FK_444839EAB5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('CREATE INDEX IDX_444839EAB5A459A0 ON visits (news_id)');
        $this->addSql('CREATE INDEX idx_created_at ON visits (created_at)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visits DROP FOREIGN KEY FK_444839EAB5A459A0');
        $this->addSql('DROP INDEX IDX_444839EAB5A459A0 ON visits');
        $this->addSql('DROP INDEX idx_created_at ON visits');
        $this->addSql('ALTER TABLE visits DROP news_id, DROP created_at');
    }
}
