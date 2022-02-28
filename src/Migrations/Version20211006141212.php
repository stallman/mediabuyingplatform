<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211006141212 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE black_list CHANGE field field VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE white_list DROP FOREIGN KEY FK_40D17B7470BEE6D');
        $this->addSql('DROP INDEX IDX_40D17B7470BEE6D ON white_list');
        $this->addSql('ALTER TABLE white_list ADD field VARCHAR(255) DEFAULT NULL, ADD group_id VARCHAR(255) DEFAULT NULL, ADD group_name VARCHAR(255) DEFAULT NULL, DROP visitor_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE black_list CHANGE field field LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE white_list ADD visitor_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\', DROP field, DROP group_id, DROP group_name');
        $this->addSql('ALTER TABLE white_list ADD CONSTRAINT FK_40D17B7470BEE6D FOREIGN KEY (visitor_id) REFERENCES visits (uuid)');
        $this->addSql('CREATE INDEX IDX_40D17B7470BEE6D ON white_list (visitor_id)');
    }
}
