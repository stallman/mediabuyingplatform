<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200518140218 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sources (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, currency_id INT NOT NULL, title VARCHAR(120) NOT NULL, multiplier NUMERIC(19, 4) NOT NULL, utm_campaign LONGTEXT DEFAULT NULL, utm_term LONGTEXT DEFAULT NULL, utm_content LONGTEXT DEFAULT NULL, subid1 LONGTEXT DEFAULT NULL, subid2 LONGTEXT DEFAULT NULL, subid3 LONGTEXT DEFAULT NULL, subid4 LONGTEXT DEFAULT NULL, subid5 LONGTEXT DEFAULT NULL, is_deleted TINYINT(1) DEFAULT \'0\', INDEX IDX_D25D65F2A76ED395 (user_id), INDEX IDX_D25D65F238248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sources ADD CONSTRAINT FK_D25D65F2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE sources ADD CONSTRAINT FK_D25D65F238248176 FOREIGN KEY (currency_id) REFERENCES currency_list (id)');;
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sources');
    }
}
