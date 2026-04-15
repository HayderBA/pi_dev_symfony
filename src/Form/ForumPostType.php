<?php

namespace App\Form;

use App\Entity\ForumPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom / Pseudo',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre nom ou pseudo']
            ])
            ->add('role', TextType::class, [
                'label' => 'Rôle',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Patient, Médecin, Aidant...']
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Anxiété' => 'Anxiété',
                    'Stress' => 'Stress',
                    'Dépression' => 'Dépression',
                    'Méditation' => 'Méditation',
                    'Sommeil' => 'Sommeil',
                    'Estime de soi' => 'Estime de soi',
                    'Général' => 'Général'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['class' => 'form-control', 'rows' => 8, 'placeholder' => 'Décrivez votre situation...']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ForumPost::class,
        ]);
    }
}