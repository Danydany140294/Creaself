<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260531223135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
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
            ALTER TABLE moyen_paiement ADD CONSTRAINT FK_ED4417D2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
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
            ALTER TABLE moyen_paiement DROP FOREIGN KEY FK_ED4417D2A76ED395
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
    }
}
