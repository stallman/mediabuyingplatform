<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200820084720 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE INDEX is_active_is_deleted ON news (is_active, is_deleted)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX is_active_is_deleted ON news');
    }
}
