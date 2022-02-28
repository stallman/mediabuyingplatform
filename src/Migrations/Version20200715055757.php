<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200715055757 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversions ADD algorithm_id INT NOT NULL');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA5BBEB6CF7 FOREIGN KEY (algorithm_id) REFERENCES algorithms (id)');
        $this->addSql('CREATE INDEX IDX_6A02DBA5BBEB6CF7 ON conversions (algorithm_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversions DROP FOREIGN KEY FK_6A02DBA5BBEB6CF7');
        $this->addSql('DROP INDEX IDX_6A02DBA5BBEB6CF7 ON conversions');
        $this->addSql('ALTER TABLE conversions DROP algorithm_id');
    }
}
