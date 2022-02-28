<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200722100522 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mediabuyer_news_rotation (id INT AUTO_INCREMENT NOT NULL, mediabuyer_id INT NOT NULL, news_id INT NOT NULL, is_rotation TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_9434467179EA3016 (mediabuyer_id), INDEX IDX_94344671B5A459A0 (news_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mediabuyer_news_rotation ADD CONSTRAINT FK_9434467179EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE mediabuyer_news_rotation ADD CONSTRAINT FK_94344671B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE mediabuyer_news DROP is_rotation');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE mediabuyer_news_rotation');
        $this->addSql('ALTER TABLE mediabuyer_news ADD is_rotation TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
