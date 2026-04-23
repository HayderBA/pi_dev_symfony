<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\WellnessTest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WellnessTestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => static fn (User $user): string => sprintf('%s (%s)', $user->getFullName(), $user->getEmail()),
                'label' => 'Utilisateur',
            ])
            ->add('typeTest', TextType::class, [
                'label' => 'Type de test',
            ])
            ->add('score', IntegerType::class, [
                'label' => 'Score',
                'attr' => ['min' => 0, 'max' => 10],
            ])
            ->add('dateTest', DateTimeType::class, [
                'label' => 'Date du test',
                'widget' => 'single_text',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WellnessTest::class,
        ]);
    }
}
