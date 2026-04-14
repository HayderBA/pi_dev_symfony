<?php

namespace App\Form;

use App\Entity\Humeurs;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HumeursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('niveau', ChoiceType::class, [
                'label' => 'Niveau humeur',
                'choices' => [
                    '1 - Très bas' => 1,
                    '2 - Bas' => 2,
                    '3 - Neutre' => 3,
                    '4 - Bon' => 4,
                    '5 - Excellent' => 5,
                ],
                'placeholder' => 'Choisir un niveau',
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Décrivez votre ressenti ou le contexte',
                ],
            ])
            ->add('dateCreation', DateTimeType::class, [
                'label' => 'Date création',
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
            'data_class' => Humeurs::class,
        ]);
    }
}
