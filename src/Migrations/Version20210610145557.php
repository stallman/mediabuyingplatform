<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210610145557 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE visits CHANGE times_of_day times_of_day CHAR(10)');
        $this->addSql('UPDATE visits SET times_of_day = DATE_FORMAT(created_at, \'%H\')');
        $this->addSql('ALTER TABLE visits CHANGE times_of_day times_of_day CHAR(2)');
    }

    public function down(Schema $schema) : void
    {
        //$this->addSql('ALTER TABLE visits CHANGE times_of_day times_of_day VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE visits CHANGE times_of_day times_of_day ENUM(\'morning\', \'afternoon\', \'evening\', \'night\')');
    }
}
