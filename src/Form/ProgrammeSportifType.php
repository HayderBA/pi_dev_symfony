<?php

namespace App\Form;

use App\Entity\ProgrammeSportif;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProgrammeSportifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => static fn (User $user): string => sprintf('%s (%s)', $user->getFullName(), $user->getEmail()),
                'label' => 'Utilisateur',
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Age',
                'attr' => ['min' => 12, 'max' => 100],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => ['Homme' => 'male', 'Femme' => 'female'],
            ])
            ->add('tailleCm', NumberType::class, [
                'label' => 'Taille (cm)',
                'scale' => 1,
                'attr' => ['step' => 0.1],
            ])
            ->add('poidsKg', NumberType::class, [
                'label' => 'Poids (kg)',
                'scale' => 1,
                'attr' => ['step' => 0.1],
            ])
            ->add('niveauStress', IntegerType::class, [
                'label' => 'Stress',
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('qualiteSommeil', IntegerType::class, [
                'label' => 'Qualite du sommeil',
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('dureeSommeilHeures', NumberType::class, [
                'label' => 'Sommeil (heures)',
                'scale' => 1,
                'attr' => ['step' => 0.1],
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
                'label' => 'Objectif',
                'choices' => [
                    'Perte de poids' => 'perte_poids',
                    'Maintien' => 'maintien',
                    'Performance' => 'performance',
                    'Recuperation' => 'recuperation',
                ],
            ])
            ->add('activiteCible', ChoiceType::class, [
                'label' => 'Activite cible',
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
