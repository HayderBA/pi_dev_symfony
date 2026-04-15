<?php

namespace App\Form;

use App\Entity\SanteBienEtre;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SanteBienEtreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('humeur', TextType::class, [
                'label' => 'Humeur',
                'required' => true,
                'attr' => [
                    'required' => true,
                    'maxlength' => 50,
                    'placeholder' => 'Ex. Calme, motivé, stressé',
                ],
            ])
            ->add('niveauStress', IntegerType::class, [
                'label' => 'Niveau stress',
                'required' => true,
                'attr' => [
                    'required' => true,
                    'min' => 1,
                    'max' => 10,
                    'placeholder' => 'De 1 à 10',
                ],
            ])
            ->add('qualiteSommeil', IntegerType::class, [
                'label' => 'Qualité sommeil',
                'required' => true,
                'attr' => [
                    'required' => true,
                    'min' => 1,
                    'max' => 10,
                    'placeholder' => 'De 1 à 10',
                ],
            ])
            ->add('nutrition', TextType::class, [
                'label' => 'Nutrition',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Résumé de vos habitudes alimentaires',
                ],
            ])
            ->add('activitePhysique', TextType::class, [
                'label' => 'Activité physique',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Sport, marche, mobilité…',
                ],
            ])
            ->add('developpementPersonnel', TextType::class, [
                'label' => 'Développement personnel',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Objectif ou pratique du moment',
                ],
            ])
            ->add('recommandations', TextareaType::class, [
                'label' => 'Recommandations',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Suggestions personnalisées ou observations',
                ],
            ])
            ->add('dateSuivi', DateType::class, [
                'label' => 'Date suivi',
                'widget' => 'single_text',
                'required' => true,
                'html5' => true,
                'attr' => [
                    'required' => true,
                ],
            ])
            ->add('dateCreation', DateTimeType::class, [
                'label' => 'Date création',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('user', EntityType::class, [
                'label' => 'Utilisateur',
                'class' => Utilisateur::class,
                'choice_label' => static fn (Utilisateur $user): string => sprintf('%s (%s)', $user->getNom(), $user->getEmail()),
                'placeholder' => 'Choisir un utilisateur',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SanteBienEtre::class,
        ]);
    }
}
