<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200629085103 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE costs (id INT AUTO_INCREMENT NOT NULL, mediabuyer_id INT NOT NULL, news_id INT NOT NULL, source_id INT NOT NULL, currency_id INT NOT NULL, date DATE NOT NULL, cost NUMERIC(9, 4) NOT NULL, is_final TINYINT(1) DEFAULT \'0\' NOT NULL, date_set_data DATE NOT NULL, INDEX IDX_AF1D57A879EA3016 (mediabuyer_id), INDEX IDX_AF1D57A8B5A459A0 (news_id), INDEX IDX_AF1D57A8953C1C61 (source_id), INDEX IDX_AF1D57A838248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE costs ADD CONSTRAINT FK_AF1D57A879EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE costs ADD CONSTRAINT FK_AF1D57A8B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE costs ADD CONSTRAINT FK_AF1D57A8953C1C61 FOREIGN KEY (source_id) REFERENCES sources (id)');
        $this->addSql('ALTER TABLE costs ADD CONSTRAINT FK_AF1D57A838248176 FOREIGN KEY (currency_id) REFERENCES currency_list (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE costs');
  }
}
