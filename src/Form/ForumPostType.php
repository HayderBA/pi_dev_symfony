<?php

namespace App\Form;

use App\Entity\ForumPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'forum.form.nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'forum.form.nom_placeholder',
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'forum.form.role',
                'choices' => [
                    'forum.roles.patient' => 'patient',
                    'forum.roles.medecin' => 'medecin',
                    'forum.roles.aidant' => 'aidant',
                    'forum.roles.visiteur' => 'visiteur',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'forum.form.categorie',
                'choices' => [
                    'forum.categories.general' => 'General',
                    'forum.categories.stress' => 'Stress',
                    'forum.categories.anxiete' => 'Anxiete',
                    'forum.categories.sommeil' => 'Sommeil',
                    'forum.categories.estime' => 'Estime de soi',
                    'forum.categories.depression' => 'Depression',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'forum.form.contenu',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 7,
                    'placeholder' => 'forum.form.contenu_placeholder',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ForumPost::class,
        ]);
    }
}
