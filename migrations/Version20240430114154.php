<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240430114154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE investissements DROP FOREIGN KEY FK_6CB119D358E0A285');
        $this->addSql('DROP INDEX IDX_6CB119D358E0A285 ON investissements');
        $this->addSql('ALTER TABLE investissements CHANGE userid_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE investissements ADD CONSTRAINT FK_6CB119D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6CB119D3A76ED395 ON investissements (user_id)');
        $this->addSql('ALTER TABLE projet RENAME INDEX idx_50159ca968d3ea09 TO IDX_50159CA9A76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE investissements DROP FOREIGN KEY FK_6CB119D3A76ED395');
        $this->addSql('DROP INDEX IDX_6CB119D3A76ED395 ON investissements');
        $this->addSql('ALTER TABLE investissements CHANGE user_id userid_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE investissements ADD CONSTRAINT FK_6CB119D358E0A285 FOREIGN KEY (userid_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6CB119D358E0A285 ON investissements (userid_id)');
        $this->addSql('ALTER TABLE projet RENAME INDEX idx_50159ca9a76ed395 TO IDX_50159CA968D3EA09');
    }
}
