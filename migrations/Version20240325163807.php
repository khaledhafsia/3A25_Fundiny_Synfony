<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240325163807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY FK_1CAD6B76A09CC87');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY FK_1CAD6B767B149D69');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY FK_1CAD6B768C92DCCA');
        $this->addSql('DROP INDEX fk_reclamations ON reclamations');
        $this->addSql('CREATE INDEX IDX_1CAD6B76A09CC87 ON reclamations (ID_Projet)');
        $this->addSql('DROP INDEX id_type_reclamation ON reclamations');
        $this->addSql('CREATE INDEX IDX_1CAD6B768C92DCCA ON reclamations (ID_Type_Reclamation)');
        $this->addSql('DROP INDEX fk_reclamation_admin ON reclamations');
        $this->addSql('CREATE INDEX IDX_1CAD6B767B149D69 ON reclamations (ID_utilisateur)');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B76A09CC87 FOREIGN KEY (ID_Projet) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B767B149D69 FOREIGN KEY (ID_utilisateur) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B768C92DCCA FOREIGN KEY (ID_Type_Reclamation) REFERENCES typesreclamation (ID_Type_Reclamation)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC67B149D69 FOREIGN KEY (ID_utilisateur) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1E512EC67B149D69 ON reponses (ID_utilisateur)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY FK_1CAD6B76A09CC87');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY FK_1CAD6B768C92DCCA');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY FK_1CAD6B767B149D69');
        $this->addSql('DROP INDEX idx_1cad6b767b149d69 ON reclamations');
        $this->addSql('CREATE INDEX fk_Reclamation_Admin ON reclamations (ID_utilisateur)');
        $this->addSql('DROP INDEX idx_1cad6b768c92dcca ON reclamations');
        $this->addSql('CREATE INDEX ID_Type_Reclamation ON reclamations (ID_Type_Reclamation)');
        $this->addSql('DROP INDEX idx_1cad6b76a09cc87 ON reclamations');
        $this->addSql('CREATE INDEX fk_Reclamations ON reclamations (ID_Projet)');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B76A09CC87 FOREIGN KEY (ID_Projet) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B768C92DCCA FOREIGN KEY (ID_Type_Reclamation) REFERENCES typesreclamation (ID_Type_Reclamation)');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B767B149D69 FOREIGN KEY (ID_utilisateur) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC67B149D69');
        $this->addSql('DROP INDEX IDX_1E512EC67B149D69 ON reponses');
    }
}
