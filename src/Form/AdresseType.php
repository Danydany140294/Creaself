<?php

namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'adresse',
                'attr' => [
                    'placeholder' => 'Ex: Domicile, Bureau...',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir un nom pour cette adresse'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('rue', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Numéro et nom de rue',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir l\'adresse'
                    ])
                ]
            ])
            ->add('complement', TextType::class, [
                'label' => 'Complément d\'adresse',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Bâtiment, appartement... (optionnel)',
                    'class' => 'form-control'
                ]
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => '75001',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir le code postal'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]{5}$/',
                        'message' => 'Le code postal doit contenir 5 chiffres'
                    ])
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'Paris',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir la ville'
                    ])
                ]
            ])
            ->add('pays', ChoiceType::class, [
                'label' => 'Pays',
                'choices' => [
                    'France' => 'France',
                    'Belgique' => 'Belgique',
                    'Suisse' => 'Suisse',
                    'Luxembourg' => 'Luxembourg',
                    'Canada' => 'Canada',
                    'Autre' => 'Autre'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'data' => 'France'
            ])
            ->add('parDefaut', CheckboxType::class, [
                'label' => 'Définir comme adresse par défaut',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}