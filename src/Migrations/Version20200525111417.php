<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200525111417 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE statistic_news (id INT AUTO_INCREMENT NOT NULL, news_id INT NOT NULL, mediabuyer_id INT DEFAULT NULL, inner_show INT DEFAULT 0 NOT NULL, inner_click INT DEFAULT 0 NOT NULL, inner_ctr NUMERIC(4, 4) DEFAULT \'0\' NOT NULL, inner_e_cpm NUMERIC(4, 4) DEFAULT \'0\' NOT NULL, click INT DEFAULT 0 NOT NULL, click_on_teaser INT DEFAULT 0 NOT NULL, probiv NUMERIC(4, 4) DEFAULT \'0\' NOT NULL, conversion NUMERIC(4, 4) DEFAULT \'0\' NOT NULL, approve_conversion NUMERIC(4, 4) DEFAULT \'0\' NOT NULL, approve INT DEFAULT 0 NOT NULL, involvement NUMERIC(4, 4) DEFAULT \'0\' NOT NULL, epc NUMERIC(4, 4) DEFAULT \'0\' NOT NULL, cr NUMERIC(4, 4) DEFAULT \'0\' NOT NULL, INDEX IDX_1AA8DD09B5A459A0 (news_id), INDEX IDX_1AA8DD0979EA3016 (mediabuyer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE statistic_news ADD CONSTRAINT FK_1AA8DD09B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE statistic_news ADD CONSTRAINT FK_1AA8DD0979EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE statistic_news');
    }
}
