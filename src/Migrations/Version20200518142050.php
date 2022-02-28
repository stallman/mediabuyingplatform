<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200518142050 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE partners (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, currency_id INT NOT NULL, title VARCHAR(120) NOT NULL, postback VARCHAR(120) NOT NULL, status_confirmed LONGTEXT NOT NULL, status_refused LONGTEXT NOT NULL, status_approved LONGTEXT NOT NULL, macros_uniq_click LONGTEXT DEFAULT NULL, macros_payment LONGTEXT DEFAULT NULL, macros_status LONGTEXT DEFAULT NULL, is_deleted TINYINT(1) DEFAULT \'0\', INDEX IDX_EFEB5164A76ED395 (user_id), INDEX IDX_EFEB516438248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE partners ADD CONSTRAINT FK_EFEB5164A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE partners ADD CONSTRAINT FK_EFEB516438248176 FOREIGN KEY (currency_id) REFERENCES currency_list (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE partners');
    }
}
