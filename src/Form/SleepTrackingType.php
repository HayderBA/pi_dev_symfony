<?php

namespace App\Form;

use App\Entity\SleepTracking;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SleepTrackingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateSommeil', DateType::class, [
                'label' => 'Date sommeil',
                'widget' => 'single_text',
            ])
            ->add('heureCoucher', TimeType::class, [
                'label' => 'Heure coucher',
                'widget' => 'single_text',
            ])
            ->add('heureReveil', TimeType::class, [
                'label' => 'Heure réveil',
                'widget' => 'single_text',
            ])
            ->add('dureeMinutes', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Ex. 420',
                ],
            ])
            ->add('qualiteSommeil', IntegerType::class, [
                'label' => 'Qualité sommeil',
                'attr' => [
                    'min' => 1,
                    'max' => 10,
                    'placeholder' => 'De 1 à 10',
                ],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ajoutez un ressenti ou un contexte utile',
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
            'data_class' => SleepTracking::class,
        ]);
    }
}
