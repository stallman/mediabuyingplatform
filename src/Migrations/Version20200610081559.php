<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200610081559 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE teasers_sub_groups (id INT AUTO_INCREMENT NOT NULL, teaser_group_id INT NOT NULL, name VARCHAR(80) NOT NULL, activity TINYINT(1) DEFAULT \'0\' NOT NULL, default_link VARCHAR(80) NOT NULL, average_percentage INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_696AD765369EEAE5 (teaser_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teasersSubGroup_NewsCategories_relations (teasers_sub_group_id INT NOT NULL, news_category_id INT NOT NULL, INDEX IDX_A8372F648E53692B (teasers_sub_group_id), INDEX IDX_A8372F643B732BAD (news_category_id), PRIMARY KEY(teasers_sub_group_id, news_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE teasers_sub_groups ADD CONSTRAINT FK_696AD765369EEAE5 FOREIGN KEY (teaser_group_id) REFERENCES teasers_groups (id)');
        $this->addSql('ALTER TABLE teasersSubGroup_NewsCategories_relations ADD CONSTRAINT FK_A8372F648E53692B FOREIGN KEY (teasers_sub_group_id) REFERENCES teasers_sub_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teasersSubGroup_NewsCategories_relations ADD CONSTRAINT FK_A8372F643B732BAD FOREIGN KEY (news_category_id) REFERENCES news_categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teasers DROP FOREIGN KEY FK_9AB3D619BDE5778E');
        $this->addSql('DROP INDEX IDX_9AB3D619BDE5778E ON teasers');
        $this->addSql('ALTER TABLE teasers CHANGE teasers_group_id teaser_sub_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE teasers ADD CONSTRAINT FK_9AB3D619211EC81F FOREIGN KEY (teaser_sub_group_id) REFERENCES teasers_sub_groups (id)');
        $this->addSql('CREATE INDEX IDX_9AB3D619211EC81F ON teasers (teaser_sub_group_id)');
        $this->addSql('ALTER TABLE teasers_groups DROP FOREIGN KEY FK_BAE58BD8727ACA70');
        $this->addSql('DROP INDEX IDX_BAE58BD8727ACA70 ON teasers_groups');
        $this->addSql('ALTER TABLE teasers_groups DROP parent_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE teasers DROP FOREIGN KEY FK_9AB3D619211EC81F');
        $this->addSql('ALTER TABLE teasersSubGroup_NewsCategories_relations DROP FOREIGN KEY FK_A8372F648E53692B');
        $this->addSql('DROP TABLE teasers_sub_groups');
        $this->addSql('DROP TABLE teasersSubGroup_NewsCategories_relations');
        $this->addSql('DROP INDEX IDX_9AB3D619211EC81F ON teasers');
        $this->addSql('ALTER TABLE teasers CHANGE teaser_sub_group_id teasers_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE teasers ADD CONSTRAINT FK_9AB3D619BDE5778E FOREIGN KEY (teasers_group_id) REFERENCES teasers_groups (id)');
        $this->addSql('CREATE INDEX IDX_9AB3D619BDE5778E ON teasers (teasers_group_id)');
        $this->addSql('ALTER TABLE teasers_groups ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE teasers_groups ADD CONSTRAINT FK_BAE58BD8727ACA70 FOREIGN KEY (parent_id) REFERENCES teasers_groups (id)');
        $this->addSql('CREATE INDEX IDX_BAE58BD8727ACA70 ON teasers_groups (parent_id)');
    }
}
