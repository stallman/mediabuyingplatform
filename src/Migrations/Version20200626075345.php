<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200626075345 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mediabuyer_algorithms (id INT AUTO_INCREMENT NOT NULL, mediabuyer_id INT NOT NULL, algorithm_id INT NOT NULL, INDEX IDX_5259AE4679EA3016 (mediabuyer_id), INDEX IDX_5259AE46BBEB6CF7 (algorithm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mediabuyer_algorithms ADD CONSTRAINT FK_5259AE4679EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE mediabuyer_algorithms ADD CONSTRAINT FK_5259AE46BBEB6CF7 FOREIGN KEY (algorithm_id) REFERENCES algorithms (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE mediabuyer_algorithms');
    }
}
