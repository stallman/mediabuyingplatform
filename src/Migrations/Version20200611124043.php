<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200611124043 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE teasers_sub_groups CHANGE activity is_active TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE teasers_groups ADD is_active TINYINT(1) DEFAULT \'0\' NOT NULL, DROP activity');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE teasers_groups  CHANGE is_active activity TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE teasers_sub_groups CHANGE is_active activity TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
