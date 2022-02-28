<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200619114026 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE teasers_click ADD design_id INT DEFAULT NULL, ADD algorithm_id INT DEFAULT NULL, CHANGE traffic_type traffic_type ENUM(\'desctop\', \'tablet\', \'mobile\'), CHANGE page_type page_type ENUM(\'full\', \'short\', \'top\')');
        $this->addSql('ALTER TABLE teasers_click ADD CONSTRAINT FK_EDE12B44E41DC9B2 FOREIGN KEY (design_id) REFERENCES designs (id)');
        $this->addSql('ALTER TABLE teasers_click ADD CONSTRAINT FK_EDE12B44BBEB6CF7 FOREIGN KEY (algorithm_id) REFERENCES algorithms (id)');
        $this->addSql('CREATE INDEX IDX_EDE12B44E41DC9B2 ON teasers_click (design_id)');
        $this->addSql('CREATE INDEX IDX_EDE12B44BBEB6CF7 ON teasers_click (algorithm_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE teasers_click DROP FOREIGN KEY FK_EDE12B44E41DC9B2');
        $this->addSql('ALTER TABLE teasers_click DROP FOREIGN KEY FK_EDE12B44BBEB6CF7');
        $this->addSql('DROP INDEX IDX_EDE12B44E41DC9B2 ON teasers_click');
        $this->addSql('DROP INDEX IDX_EDE12B44BBEB6CF7 ON teasers_click');
        $this->addSql('ALTER TABLE teasers_click DROP design_id, DROP algorithm_id, CHANGE traffic_type traffic_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE page_type page_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
