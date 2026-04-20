<?php
// src/Form/EvenementType.php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'attr' => ['class' => 'form-control-modern', 'placeholder' => 'Titre de l\'événement']
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control-modern', 'rows' => 5, 'placeholder' => 'Description détaillée...', 'style' => 'border-radius: 20px;']
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-control-modern']
            ])
            ->add('localisation', TextType::class, [
                'attr' => ['class' => 'form-control-modern', 'placeholder' => 'Lieu de l\'événement']
            ])
            ->add('maxCapacity', NumberType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control-modern', 'min' => 1, 'placeholder' => 'Capacité maximale']
            ])
            ->add('basePrice', NumberType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control-modern', 'min' => 0, 'step' => 1, 'placeholder' => 'Prix de base']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}