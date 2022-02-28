<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200519122409 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql( 'ALTER TABLE `teasers_groups` DROP FOREIGN KEY `parent_id`;  ALTER TABLE `teasers_groups` ADD CONSTRAINT `fk_parent_id` FOREIGN KEY (`fk_parent_id`) REFERENCES `teasers_groups` (`t2`) ON DELETE RESTRICT ; ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `teasers_groups` DROP FOREIGN KEY `parent_id`;  ALTER TABLE `teasers_groups` ADD CONSTRAINT `fk_parent_id` FOREIGN KEY (`fk_parent_id`) REFERENCES `teasers_groups` (`t2`) ON DELETE CASCADE;');
    }
}
