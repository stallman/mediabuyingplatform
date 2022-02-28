<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200623072350 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
       $this->addSql('ALTER TABLE statistic_promo_block_teasers ADD algorithm_id INT DEFAULT NULL, ADD design_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers ADD CONSTRAINT FK_EA8000F8BBEB6CF7 FOREIGN KEY (algorithm_id) REFERENCES algorithms (id)');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers ADD CONSTRAINT FK_EA8000F8E41DC9B2 FOREIGN KEY (design_id) REFERENCES designs (id)');
        $this->addSql('CREATE INDEX IDX_EA8000F8BBEB6CF7 ON statistic_promo_block_teasers (algorithm_id)');
        $this->addSql('CREATE INDEX IDX_EA8000F8E41DC9B2 ON statistic_promo_block_teasers (design_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE statistic_promo_block_teasers DROP FOREIGN KEY FK_EA8000F8BBEB6CF7');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers DROP FOREIGN KEY FK_EA8000F8E41DC9B2');
        $this->addSql('DROP INDEX IDX_EA8000F8BBEB6CF7 ON statistic_promo_block_teasers');
        $this->addSql('DROP INDEX IDX_EA8000F8E41DC9B2 ON statistic_promo_block_teasers');
        $this->addSql('ALTER TABLE statistic_promo_block_teasers DROP algorithm_id, DROP design_id');
    }
}
