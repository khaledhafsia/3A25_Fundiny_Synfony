<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240425144002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC67B149D69');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_idreclmations');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_iduser');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC67B149D69');
        $this->addSql('DROP INDEX fk_idreclmations ON reponses');
        $this->addSql('CREATE INDEX IDX_1E512EC62EF41509 ON reponses (ID_Reclamation)');
        $this->addSql('DROP INDEX fk_iduser ON reponses');
        $this->addSql('CREATE INDEX IDX_1E512EC67B149D69 ON reponses (ID_utilisateur)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_idreclmations FOREIGN KEY (ID_Reclamation) REFERENCES reclamations (ID_Reclamation)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_iduser FOREIGN KEY (ID_utilisateur) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC67B149D69 FOREIGN KEY (ID_utilisateur) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD roles JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC62EF41509');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC67B149D69');
        $this->addSql('DROP INDEX idx_1e512ec67b149d69 ON reponses');
        $this->addSql('CREATE INDEX FK_iduser ON reponses (ID_utilisateur)');
        $this->addSql('DROP INDEX idx_1e512ec62ef41509 ON reponses');
        $this->addSql('CREATE INDEX FK_idreclmations ON reponses (ID_Reclamation)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC62EF41509 FOREIGN KEY (ID_Reclamation) REFERENCES reclamations (ID_Reclamation)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC67B149D69 FOREIGN KEY (ID_utilisateur) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user DROP roles');
    }
}
