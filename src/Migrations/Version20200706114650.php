<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200706114650 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE show_news (id INT AUTO_INCREMENT NOT NULL, news_id INT NOT NULL, mediabuyer_id INT NOT NULL, algorithm_id INT DEFAULT NULL, design_id INT NOT NULL, source_id INT DEFAULT NULL, page_type ENUM(\'full\', \'short\'), uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, INDEX IDX_C1244F93B5A459A0 (news_id), INDEX IDX_C1244F9363147990 (mediabuyer_id), INDEX IDX_C1244F93BBEB6CF7 (algorithm_id), INDEX IDX_C1244F93E41DC9B2 (design_id), INDEX IDX_C1244F93953C1C61 (source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE show_news ADD CONSTRAINT FK_C1244F93B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE show_news ADD CONSTRAINT FK_C1244F9363147990 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE show_news ADD CONSTRAINT FK_C1244F93BBEB6CF7 FOREIGN KEY (algorithm_id) REFERENCES algorithms (id)');
        $this->addSql('ALTER TABLE show_news ADD CONSTRAINT FK_C1244F93E41DC9B2 FOREIGN KEY (design_id) REFERENCES designs (id)');
        $this->addSql('ALTER TABLE show_news ADD CONSTRAINT FK_C1244F93953C1C61 FOREIGN KEY (source_id) REFERENCES sources (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE show_news');
    }
}
