<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200610081802 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE teasersSubGroup_NewsCategories_relations RENAME INDEX idx_a8372f648e53692b TO IDX_56E1D11E8E53692B');
        $this->addSql('ALTER TABLE teasersSubGroup_NewsCategories_relations RENAME INDEX idx_a8372f643b732bad TO IDX_56E1D11E3B732BAD');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE teasersSubGroup_newsCategories_relations RENAME INDEX idx_56e1d11e8e53692b TO IDX_A8372F648E53692B');
        $this->addSql('ALTER TABLE teasersSubGroup_newsCategories_relations RENAME INDEX idx_56e1d11e3b732bad TO IDX_A8372F643B732BAD');
    }
}
