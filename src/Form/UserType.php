<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType ;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;



class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('second_name')
            ->add('age')
            ->add('gender', ChoiceType::class, [
                    'choices' => [
                        'Homme' => 'homme',
                        'Femme' => 'femme',
                    ],
                    'placeholder' => 'Choisir genre',
                ])
            ->add('phone_number')
            ->add('birth_date', DateType::class, [
                    'widget' => 'single_text',
                    'input' => 'string', 
                ])
            ->add('email')
            ->add('password')
        ->add('role', ChoiceType::class, [
            'choices' => [
                'Patient' => 'patient',
                'Doctor' => 'doctor',
            ],
            'placeholder' => 'Choisir rôle',
        ])
        ->add('blood_type', TextType::class,[
            'required' => false,
            'mapped' => false
        ])
        ->add('weight', NumberType::class, [
            'required' => false,
            'mapped' => false
        ])
        ->add('height', NumberType::class, [
            'required' => false,
            'mapped' => false
        ])

        // 🔥 CHAMPS DOCTOR
        ->add('specialty', TextType::class, [
            'required' => false,
            'mapped' => false
        ])
        ->add('experience', NumberType::class, [
            'required' => false,
            'mapped' => false
        ])
        ->add('diplome', TextType::class, [
            'required' => false,
            'mapped' => false
        ])
        ->add('disponible', ChoiceType::class, [
        'choices' => [
            'Disponible' => 1,
            'Non disponible' => 0,
        ],
        'required' => false,
        'mapped' => false
        ])

        ->add('actif', ChoiceType::class, [
            'choices' => [
                'Actif' => 1,
                'Inactif' => 0,
            ],
            'required' => false,
            'mapped' => false
        ])
        ->add('tarif_consultation', NumberType::class, [
            'required' => false,
            'mapped' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
