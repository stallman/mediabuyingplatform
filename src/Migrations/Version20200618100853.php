<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200618100853 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news_click ADD news_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_C049457EB5A459A0 ON news_click (news_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news_click DROP FOREIGN KEY FK_C049457EB5A459A0');
        $this->addSql('DROP INDEX IDX_C049457EB5A459A0 ON news_click');
        $this->addSql('ALTER TABLE news_click DROP news_id');
    }
}
