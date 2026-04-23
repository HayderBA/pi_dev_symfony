<?php

namespace App\Form;

use App\Entity\SanteBienEtre;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SanteBienEtreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => static fn (User $user): string => sprintf('%s (%s)', $user->getFullName(), $user->getEmail()),
                'label' => 'Utilisateur',
            ])
            ->add('humeur', TextType::class, [
                'label' => 'Humeur',
            ])
            ->add('niveauStress', IntegerType::class, [
                'label' => 'Niveau de stress',
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('qualiteSommeil', IntegerType::class, [
                'label' => 'Qualite du sommeil',
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('nutrition', TextType::class, [
                'label' => 'Nutrition',
                'required' => false,
            ])
            ->add('activitePhysique', TextType::class, [
                'label' => 'Activite physique',
                'required' => false,
            ])
            ->add('developpementPersonnel', TextType::class, [
                'label' => 'Developpement personnel',
                'required' => false,
            ])
            ->add('recommandations', TextareaType::class, [
                'label' => 'Recommandations',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('dateSuivi', DateType::class, [
                'label' => 'Date de suivi',
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SanteBienEtre::class,
        ]);
    }
}
