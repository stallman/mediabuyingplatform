<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200617092440 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE postback (id INT AUTO_INCREMENT NOT NULL, affiliate_id INT NOT NULL, click_id INT NOT NULL, status ENUM(\'wait\', \'reject\', \'success\', \'new\'), payout VARCHAR(120) DEFAULT NULL, currency_code VARCHAR(3) NOT NULL, fulldata LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_79E255BE9F12C49A (affiliate_id), INDEX IDX_79E255BEF31E618F (click_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE postback ADD CONSTRAINT FK_79E255BE9F12C49A FOREIGN KEY (affiliate_id) REFERENCES partners (id)');
        $this->addSql('ALTER TABLE postback ADD CONSTRAINT FK_79E255BEF31E618F FOREIGN KEY (click_id) REFERENCES teasers_click (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE postback');
    }
}
