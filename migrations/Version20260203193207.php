<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203193207 extends AbstractMigration
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
            CREATE TABLE atelier (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, descrption VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, produit_id INT NOT NULL, commande_id INT DEFAULT NULL, note SMALLINT NOT NULL, commentaire LONGTEXT DEFAULT NULL, date_avis DATETIME NOT NULL, approuve TINYINT(1) DEFAULT 0 NOT NULL, visible TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_8F91ABF0A76ED395 (user_id), INDEX IDX_8F91ABF0F347EFB (produit_id), INDEX IDX_8F91ABF082EA2E54 (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE box (id INT AUTO_INCREMENT NOT NULL, createur_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, description VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, stock INT NOT NULL, type VARCHAR(50) NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, date_creation DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_8A9483A73A201E5 (createur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE box_produit (box_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_30677216D8177B3F (box_id), INDEX IDX_30677216F347EFB (produit_id), PRIMARY KEY(box_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, adresse_livraison_id INT DEFAULT NULL, numero_commande VARCHAR(50) NOT NULL, date_commande DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, total_ttc DOUBLE PRECISION NOT NULL, date_livraison DATETIME DEFAULT NULL, date_expedition DATETIME DEFAULT NULL, INDEX IDX_6EEAA67DA76ED395 (user_id), INDEX IDX_6EEAA67DBE2F0A35 (adresse_livraison_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE composition_box_personnalisable (id INT AUTO_INCREMENT NOT NULL, ligne_commande_id INT NOT NULL, produit_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_1E78DBE9E10FEE63 (ligne_commande_id), INDEX IDX_1E78DBE9F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE composition_panier_personnalisable (id INT AUTO_INCREMENT NOT NULL, ligne_panier_id INT NOT NULL, produit_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_B42582D838989DF4 (ligne_panier_id), INDEX IDX_B42582D8F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ligne_commande (id INT AUTO_INCREMENT NOT NULL, commande_id INT NOT NULL, produit_id INT DEFAULT NULL, box_id INT DEFAULT NULL, quantite INT NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, INDEX IDX_3170B74B82EA2E54 (commande_id), INDEX IDX_3170B74BF347EFB (produit_id), INDEX IDX_3170B74BD8177B3F (box_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ligne_panier (id INT AUTO_INCREMENT NOT NULL, panier_id INT NOT NULL, produit_id INT DEFAULT NULL, box_id INT DEFAULT NULL, quantite INT NOT NULL, INDEX IDX_21691B4F77D927C (panier_id), INDEX IDX_21691B4F347EFB (produit_id), INDEX IDX_21691B4D8177B3F (box_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date_creation DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', date_expiration DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_24CC0DF2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, description LONGTEXT NOT NULL, stock INT NOT NULL, image VARCHAR(255) NOT NULL, disponible TINYINT(1) DEFAULT 1 NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, date_naissance DATE DEFAULT NULL, points_fidelite INT DEFAULT 0 NOT NULL, date_inscription DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_favoris (user_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_D13EDA38A76ED395 (user_id), INDEX IDX_D13EDA38F347EFB (produit_id), PRIMARY KEY(user_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ALTER TABLE box ADD CONSTRAINT FK_8A9483A73A201E5 FOREIGN KEY (createur_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box_produit ADD CONSTRAINT FK_30677216D8177B3F FOREIGN KEY (box_id) REFERENCES box (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box_produit ADD CONSTRAINT FK_30677216F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DBE2F0A35 FOREIGN KEY (adresse_livraison_id) REFERENCES adresse (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_box_personnalisable ADD CONSTRAINT FK_1E78DBE9E10FEE63 FOREIGN KEY (ligne_commande_id) REFERENCES ligne_commande (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_box_personnalisable ADD CONSTRAINT FK_1E78DBE9F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_panier_personnalisable ADD CONSTRAINT FK_B42582D838989DF4 FOREIGN KEY (ligne_panier_id) REFERENCES ligne_panier (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_panier_personnalisable ADD CONSTRAINT FK_B42582D8F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BD8177B3F FOREIGN KEY (box_id) REFERENCES box (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier ADD CONSTRAINT FK_21691B4F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier ADD CONSTRAINT FK_21691B4F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier ADD CONSTRAINT FK_21691B4D8177B3F FOREIGN KEY (box_id) REFERENCES box (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favoris ADD CONSTRAINT FK_D13EDA38A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_favoris ADD CONSTRAINT FK_D13EDA38F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
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
            ALTER TABLE box DROP FOREIGN KEY FK_8A9483A73A201E5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box_produit DROP FOREIGN KEY FK_30677216D8177B3F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box_produit DROP FOREIGN KEY FK_30677216F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DBE2F0A35
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_box_personnalisable DROP FOREIGN KEY FK_1E78DBE9E10FEE63
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_box_personnalisable DROP FOREIGN KEY FK_1E78DBE9F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_panier_personnalisable DROP FOREIGN KEY FK_B42582D838989DF4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_panier_personnalisable DROP FOREIGN KEY FK_B42582D8F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B82EA2E54
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BF347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BD8177B3F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier DROP FOREIGN KEY FK_21691B4F77D927C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier DROP FOREIGN KEY FK_21691B4F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier DROP FOREIGN KEY FK_21691B4D8177B3F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395
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
            DROP TABLE atelier
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE avis
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE box
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE box_produit
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commande
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE composition_box_personnalisable
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE composition_panier_personnalisable
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ligne_commande
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ligne_panier
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE panier
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE produit
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_request
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `user`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_favoris
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
