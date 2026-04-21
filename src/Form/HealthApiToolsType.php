<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class HealthApiToolsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'scale' => 1,
                'data' => 70.0,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Positive(),
                ],
                'attr' => [
                    'min' => 1,
                    'step' => 0.1,
                    'placeholder' => 'Ex. 70',
                ],
            ])
            ->add('taille', NumberType::class, [
                'label' => 'Taille (m)',
                'scale' => 2,
                'data' => 1.75,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Positive(),
                ],
                'help' => 'La formule IMC utilise la taille en metres.',
                'attr' => [
                    'min' => 0.5,
                    'step' => 0.01,
                    'placeholder' => 'Ex. 1.75',
                ],
            ])
            ->add('nutritionQuery', TextareaType::class, [
                'label' => 'Requete nutritionnelle',
                'data' => '1 apple and 1 banana',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ],
                'help' => 'Exemple: 1 apple and 1 banana',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ex. 1 apple and 1 banana',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
        ]);
    }
}
