<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200609122304 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE teasers_click (id INT AUTO_INCREMENT NOT NULL, buyer_id INT NOT NULL, source_id INT DEFAULT NULL, teaser_id INT NOT NULL, news_id INT DEFAULT NULL, counry_code_id INT DEFAULT NULL, traffic_type ENUM(\'desctop\', \'tablet\', \'mobile\'), page_type ENUM(\'full\', \'short\', \'top\'), user_ip VARCHAR(39) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_EDE12B446C755722 (buyer_id), INDEX IDX_EDE12B44953C1C61 (source_id), INDEX IDX_EDE12B447ADE9C9E (teaser_id), INDEX IDX_EDE12B44B5A459A0 (news_id), INDEX IDX_EDE12B442EC377AC (counry_code_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE teasers_click ADD CONSTRAINT FK_EDE12B446C755722 FOREIGN KEY (buyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE teasers_click ADD CONSTRAINT FK_EDE12B44953C1C61 FOREIGN KEY (source_id) REFERENCES sources (id)');
        $this->addSql('ALTER TABLE teasers_click ADD CONSTRAINT FK_EDE12B447ADE9C9E FOREIGN KEY (teaser_id) REFERENCES teasers (id)');
        $this->addSql('ALTER TABLE teasers_click ADD CONSTRAINT FK_EDE12B44B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE teasers_click ADD CONSTRAINT FK_EDE12B442EC377AC FOREIGN KEY (counry_code_id) REFERENCES geo (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE teasers_click');
    }
}
