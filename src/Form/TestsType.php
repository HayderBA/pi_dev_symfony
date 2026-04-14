<?php

namespace App\Form;

use App\Entity\Tests;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeTest', TextType::class, [
                'label' => 'Type test',
                'attr' => [
                    'placeholder' => 'Ex. Stress, anxiété, sommeil',
                ],
            ])
            ->add('score', IntegerType::class, [
                'label' => 'Score',
                'attr' => [
                    'min' => 0,
                    'max' => 10,
                    'placeholder' => 'De 0 à 10',
                ],
            ])
            ->add('dateTest', DateTimeType::class, [
                'label' => 'Date test',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('utilisateur', EntityType::class, [
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
            'data_class' => Tests::class,
        ]);
    }
}
