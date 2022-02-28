<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200609134253 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE teasers_click DROP FOREIGN KEY FK_EDE12B442EC377AC');
        $this->addSql('DROP INDEX IDX_EDE12B442EC377AC ON teasers_click');
        $this->addSql('ALTER TABLE teasers_click ADD country_code VARCHAR(39) DEFAULT NULL, DROP counry_code_id, CHANGE traffic_type traffic_type ENUM(\'desctop\', \'tablet\', \'mobile\'), CHANGE page_type page_type ENUM(\'full\', \'short\', \'top\')');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE teasers_click ADD counry_code_id INT DEFAULT NULL, DROP country_code, CHANGE traffic_type traffic_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE page_type page_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE teasers_click ADD CONSTRAINT FK_EDE12B442EC377AC FOREIGN KEY (counry_code_id) REFERENCES geo (id)');
        $this->addSql('CREATE INDEX IDX_EDE12B442EC377AC ON teasers_click (counry_code_id)');
    }
}
