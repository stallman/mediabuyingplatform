<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200529084926 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE conversions (id INT AUTO_INCREMENT NOT NULL, mediabuyer_id INT NOT NULL, affilate_id INT NOT NULL, source_id INT NOT NULL, subgroup_id INT NOT NULL, country_id INT NOT NULL, currency_id INT NOT NULL, click_id BIGINT NOT NULL, status VARCHAR(120) NOT NULL, amount NUMERIC(10, 4) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, is_deleted TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_6A02DBA579EA3016 (mediabuyer_id), INDEX IDX_6A02DBA5D0ED68EF (affilate_id), INDEX IDX_6A02DBA5953C1C61 (source_id), INDEX IDX_6A02DBA5F5C464CE (subgroup_id), INDEX IDX_6A02DBA5F92F3E70 (country_id), INDEX IDX_6A02DBA538248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA579EA3016 FOREIGN KEY (mediabuyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA5D0ED68EF FOREIGN KEY (affilate_id) REFERENCES partners (id)');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA5953C1C61 FOREIGN KEY (source_id) REFERENCES sources (id)');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA5F5C464CE FOREIGN KEY (subgroup_id) REFERENCES teasers_groups (id)');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA5F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE conversions ADD CONSTRAINT FK_6A02DBA538248176 FOREIGN KEY (currency_id) REFERENCES currency_list (id)');
        $this->addSql('ALTER TABLE teasers CHANGE text text VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE conversions');
        $this->addSql('ALTER TABLE teasers CHANGE text text VARCHAR(120) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
