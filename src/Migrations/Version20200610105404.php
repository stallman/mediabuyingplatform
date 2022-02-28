<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200610105404 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE teasers_sub_group_settings (id INT AUTO_INCREMENT NOT NULL, teasers_sub_group_id INT NOT NULL, geo_code VARCHAR(2) DEFAULT NULL, link VARCHAR(255) NOT NULL, approve_average_percentage INT NOT NULL, INDEX IDX_4AA27B668E53692B (teasers_sub_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE teasers_sub_group_settings ADD CONSTRAINT FK_4AA27B668E53692B FOREIGN KEY (teasers_sub_group_id) REFERENCES teasers_sub_groups (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE teasers_sub_group_settings');
    }
}
