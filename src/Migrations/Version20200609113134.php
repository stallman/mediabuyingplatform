<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200609113134 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE statistic_teasers (id INT AUTO_INCREMENT NOT NULL, teaser_id INT NOT NULL, teaser_show INT DEFAULT 0 NOT NULL, click INT DEFAULT 0 NOT NULL, conversion NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, approve_conversion NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, approve INT DEFAULT 0 NOT NULL, e_cpm NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, epc NUMERIC(8, 4) DEFAULT \'0\' NOT NULL, ctr NUMERIC(7, 4) DEFAULT \'0\' NOT NULL, cr NUMERIC(8, 4) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_AA1CE1947ADE9C9E (teaser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE statistic_teasers ADD CONSTRAINT FK_AA1CE1947ADE9C9E FOREIGN KEY (teaser_id) REFERENCES teasers (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE statistic_teasers');
    }
}
