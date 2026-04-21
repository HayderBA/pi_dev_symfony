<?php

namespace App\ProgrammeSportifBundle\Form;

use App\Entity\Utilisateur;
use App\ProgrammeSportifBundle\Entity\ProgrammeSportif;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProgrammeSportifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'label' => 'Utilisateur',
                'class' => Utilisateur::class,
                'placeholder' => 'Choisir un utilisateur',
                'choice_label' => static fn (Utilisateur $user): string => sprintf('%s (%s)', $user->getNom(), $user->getEmail()),
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Age',
                'attr' => [
                    'min' => 12,
                    'max' => 100,
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Homme' => 'male',
                    'Femme' => 'female',
                ],
            ])
            ->add('tailleCm', NumberType::class, [
                'label' => 'Taille (cm)',
                'scale' => 1,
                'attr' => [
                    'min' => 120,
                    'max' => 230,
                    'step' => 0.1,
                ],
            ])
            ->add('poidsKg', NumberType::class, [
                'label' => 'Poids (kg)',
                'scale' => 1,
                'attr' => [
                    'min' => 35,
                    'max' => 250,
                    'step' => 0.1,
                ],
            ])
            ->add('niveauStress', IntegerType::class, [
                'label' => 'Stress (1 a 10)',
                'attr' => [
                    'min' => 1,
                    'max' => 10,
                ],
            ])
            ->add('qualiteSommeil', IntegerType::class, [
                'label' => 'Qualite du sommeil (1 a 10)',
                'attr' => [
                    'min' => 1,
                    'max' => 10,
                ],
            ])
            ->add('dureeSommeilHeures', NumberType::class, [
                'label' => 'Duree de sommeil (heures)',
                'scale' => 1,
                'attr' => [
                    'min' => 3,
                    'max' => 12,
                    'step' => 0.1,
                ],
            ])
            ->add('niveauActivite', ChoiceType::class, [
                'label' => 'Niveau d activite',
                'choices' => [
                    'Sedentaire' => 'sedentary',
                    'Legere' => 'light',
                    'Moderee' => 'moderate',
                    'Active' => 'active',
                    'Tres active' => 'veryactive',
                ],
            ])
            ->add('objectif', ChoiceType::class, [
                'label' => 'Objectif principal',
                'choices' => [
                    'Perte de poids' => 'perte_poids',
                    'Maintien' => 'maintien',
                    'Performance' => 'performance',
                    'Recuperation' => 'recuperation',
                ],
            ])
            ->add('activiteCible', ChoiceType::class, [
                'label' => 'Activite preferee',
                'choices' => [
                    'Marche rapide' => 'walking',
                    'Jogging' => 'running',
                    'Cyclisme' => 'cycling',
                    'Yoga' => 'yoga',
                    'Natation' => 'swimming',
                    'Renforcement' => 'strength training',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProgrammeSportif::class,
        ]);
    }
}
