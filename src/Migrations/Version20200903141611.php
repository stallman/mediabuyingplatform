<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200903141611 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs$this->addSql('ALTER TABLE statistic_teasers DROP FOREIGN KEY FK_AA1CE194BBEB6CF7');
        $this->addSql('ALTER TABLE statistic_teasers DROP FOREIGN KEY FK_AA1CE194E41DC9B2');
        $this->addSql('DROP INDEX IDX_AA1CE194E41DC9B2 ON statistic_teasers');
        $this->addSql('DROP INDEX IDX_AA1CE194BBEB6CF7 ON statistic_teasers');
        $this->addSql('ALTER TABLE statistic_teasers DROP design_id, DROP algorithm_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs$this->addSql('ALTER TABLE statistic_teasers ADD design_id INT DEFAULT NULL, ADD algorithm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE statistic_teasers ADD CONSTRAINT FK_AA1CE194BBEB6CF7 FOREIGN KEY (algorithm_id) REFERENCES algorithms (id)');
        $this->addSql('ALTER TABLE statistic_teasers ADD CONSTRAINT FK_AA1CE194E41DC9B2 FOREIGN KEY (design_id) REFERENCES designs (id)');
        $this->addSql('CREATE INDEX IDX_AA1CE194E41DC9B2 ON statistic_teasers (design_id)');
        $this->addSql('CREATE INDEX IDX_AA1CE194BBEB6CF7 ON statistic_teasers (algorithm_id)');
    }
}
