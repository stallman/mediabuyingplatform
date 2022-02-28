<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200513103737 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mediabuyer_news (id INT AUTO_INCREMENT NOT NULL, mediabuyer_id INT NOT NULL, news_id INT NOT NULL, drop_teasers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_4EFF735779EA3016 (mediabuyer_id), INDEX IDX_4EFF7357B5A459A0 (news_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mediabuyer_news ADD CONSTRAINT FK_4EFF735779EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE mediabuyer_news ADD CONSTRAINT FK_4EFF7357B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE news_countries_relations (news_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_C2CDD0C9B5A459A0 (news_id), INDEX IDX_C2CDD0C9F92F3E70 (country_id), PRIMARY KEY(news_id, country_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE news_countries_relations ADD CONSTRAINT FK_C2CDD0C9B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_countries_relations ADD CONSTRAINT FK_C2CDD0C9F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE');
    }
}
