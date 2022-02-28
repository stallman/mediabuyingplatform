<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200810054513 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visits ADD times_of_day ENUM(\'morning\', \'afternoon\', \'evening\', \'night\'), ADD day_of_week ENUM(\'sunday\', \'monday\', \'tuesday\', \'wednesday\', \'thursday\', \'friday\', \'saturday\')');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visits DROP times_of_day, DROP day_of_week');
    }
}
