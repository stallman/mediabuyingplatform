<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200610110808 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE teasers_sub_group_settings ADD CONSTRAINT FK_4AA27B6673321F98 FOREIGN KEY (geo_code) REFERENCES country (iso_code)');
        $this->addSql('CREATE INDEX IDX_4AA27B6673321F98 ON teasers_sub_group_settings (geo_code)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE teasers_sub_group_settings DROP FOREIGN KEY FK_4AA27B6673321F98');
        $this->addSql('DROP INDEX IDX_4AA27B6673321F98 ON teasers_sub_group_settings');
    }
}
