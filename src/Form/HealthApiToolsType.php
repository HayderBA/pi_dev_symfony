<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class HealthApiToolsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'scale' => 1,
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                ],
                'data' => 70.0,
                'attr' => ['step' => 0.1],
            ])
            ->add('taille', NumberType::class, [
                'label' => 'Taille (m)',
                'scale' => 2,
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                ],
                'data' => 1.75,
                'attr' => ['step' => 0.01],
                'help' => 'La formule IMC utilise la taille en metres.',
            ])
            ->add('nutritionQuery', TextType::class, [
                'label' => 'Requete nutritionnelle',
                'constraints' => [
                    new NotBlank(),
                ],
                'data' => '1 sandwich and 1 soda',
                'help' => 'Exemple: 1 apple and 1 banana',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
