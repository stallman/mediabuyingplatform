<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200812080547 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE partners ADD status_declined LONGTEXT NOT NULL, ADD status_pending LONGTEXT NOT NULL, DROP status_confirmed, DROP status_refused');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE partners ADD status_confirmed LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD status_refused LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP status_declined, DROP status_pending');
    }
}
