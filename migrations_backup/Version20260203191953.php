<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203191953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE adresse (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nom VARCHAR(100) NOT NULL, rue VARCHAR(255) NOT NULL, code_postal VARCHAR(10) NOT NULL, ville VARCHAR(100) NOT NULL, pays VARCHAR(100) NOT NULL, complement VARCHAR(255) DEFAULT NULL, par_defaut TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_C35F0816A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, produit_id INT NOT NULL, commande_id INT DEFAULT NULL, note SMALLINT NOT NULL, commentaire LONGTEXT DEFAULT NULL, date_avis DATETIME NOT NULL, approuve TINYINT(1) DEFAULT 0 NOT NULL, visible TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_8F91ABF0A76ED395 (user_id), INDEX IDX_8F91ABF0F347EFB (produit_id), INDEX IDX_8F91ABF082EA2E54 (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_favoris (user_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_D13EDA38A76ED395 (user_id), INDEX IDX_D13EDA38F347EFB (produit_id), PRIMARY KEY(user_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE adresse ADD CONSTRAINT FK_C35F0816A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF082EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favoris ADD CONSTRAINT FK_D13EDA38A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favoris ADD CONSTRAINT FK_D13EDA38F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD adresse_livraison_id INT DEFAULT NULL, ADD date_livraison DATETIME DEFAULT NULL, ADD date_expedition DATETIME DEFAULT NULL, CHANGE user_id user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DBE2F0A35 FOREIGN KEY (adresse_livraison_id) REFERENCES adresse (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EEAA67DBE2F0A35 ON commande (adresse_livraison_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD date_creation DATETIME NOT NULL, ADD date_modification DATETIME DEFAULT NULL, CHANGE disponible disponible TINYINT(1) DEFAULT 1 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD avatar VARCHAR(255) DEFAULT NULL, ADD date_naissance DATE DEFAULT NULL, ADD points_fidelite INT DEFAULT 0 NOT NULL, ADD date_inscription DATETIME NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DBE2F0A35
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE adresse DROP FOREIGN KEY FK_C35F0816A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF082EA2E54
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favoris DROP FOREIGN KEY FK_D13EDA38A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favoris DROP FOREIGN KEY FK_D13EDA38F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE adresse
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE avis
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_favoris
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6EEAA67DBE2F0A35 ON commande
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP adresse_livraison_id, DROP date_livraison, DROP date_expedition, CHANGE user_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP date_creation, DROP date_modification, CHANGE disponible disponible TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` DROP avatar, DROP date_naissance, DROP points_fidelite, DROP date_inscription
        SQL);
    }
}
