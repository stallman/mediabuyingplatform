<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200619080703 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD39950C54C8C93');
        $this->addSql('DROP TABLE news_types');
        $this->addSql('DROP INDEX IDX_1DD39950C54C8C93 ON news');
        $this->addSql('ALTER TABLE news ADD type ENUM(\'own\', \'common\'), DROP type_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE news_types (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE geo CHANGE city_name_ru city_name_ru VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE news ADD type_id INT NOT NULL, DROP type');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD39950C54C8C93 FOREIGN KEY (type_id) REFERENCES news_types (id)');
        $this->addSql('CREATE INDEX IDX_1DD39950C54C8C93 ON news (type_id)');
    }
}
