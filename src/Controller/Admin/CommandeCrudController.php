<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Enum\CommandeStatut;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class CommandeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commande::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            TextField::new('numeroCommande', 'N° Commande'),
            
            // C'est "user" et non "utilisateur"
            AssociationField::new('user', 'Client'),
            
            ChoiceField::new('statut', 'Statut')
                ->setChoices([
                    'En attente' => CommandeStatut::EN_ATTENTE->value,
                    'Payée' => CommandeStatut::PAYEE->value,
                    'Confirmée' => CommandeStatut::CONFIRMEE->value,
                    'En préparation' => CommandeStatut::EN_PREPARATION->value,
                    'Expédiée' => CommandeStatut::EXPEDIEE->value,
                    'Livrée' => CommandeStatut::LIVREE->value,
                    'Annulée' => CommandeStatut::ANNULEE->value,
                ])
                ->renderAsBadges([
                    CommandeStatut::EN_ATTENTE->value => 'warning',
                    CommandeStatut::PAYEE->value => 'success',
                    CommandeStatut::CONFIRMEE->value => 'info',
                    CommandeStatut::EN_PREPARATION->value => 'primary',
                    CommandeStatut::EXPEDIEE->value => 'secondary',
                    CommandeStatut::LIVREE->value => 'success',
                    CommandeStatut::ANNULEE->value => 'danger',
                ]),
            
            MoneyField::new('totalTTC', 'Total TTC')->setCurrency('EUR'),
            
            DateTimeField::new('dateCommande', 'Date de commande'),
            
            CollectionField::new('lignesCommande', 'Lignes de commande')
                ->hideOnIndex()
                ->onlyOnDetail(),
        ];
    }
}