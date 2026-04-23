<?php

namespace App\Form;

use App\Entity\Humeur;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HumeurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => static fn (User $user): string => sprintf('%s (%s)', $user->getFullName(), $user->getEmail()),
                'label' => 'Utilisateur',
            ])
            ->add('niveau', ChoiceType::class, [
                'label' => 'Niveau d humeur',
                'choices' => [
                    '1 - Tres bas' => 1,
                    '2 - Bas' => 2,
                    '3 - Neutre' => 3,
                    '4 - Bon' => 4,
                    '5 - Excellent' => 5,
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Humeur::class,
        ]);
    }
}
