<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200804121346 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE black_list (id INT AUTO_INCREMENT NOT NULL, buyer_id INT NOT NULL, visitor_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_972CB8516C755722 (buyer_id), INDEX IDX_972CB85170BEE6D (visitor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE white_list (id INT AUTO_INCREMENT NOT NULL, buyer_id INT NOT NULL, visitor_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_40D17B746C755722 (buyer_id), INDEX IDX_40D17B7470BEE6D (visitor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE black_list ADD CONSTRAINT FK_972CB8516C755722 FOREIGN KEY (buyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE black_list ADD CONSTRAINT FK_972CB85170BEE6D FOREIGN KEY (visitor_id) REFERENCES visits (uuid)');
        $this->addSql('ALTER TABLE white_list ADD CONSTRAINT FK_40D17B746C755722 FOREIGN KEY (buyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE white_list ADD CONSTRAINT FK_40D17B7470BEE6D FOREIGN KEY (visitor_id) REFERENCES visits (uuid)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE black_list');
        $this->addSql('DROP TABLE white_list');
    }
}
