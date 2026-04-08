<?php

namespace App\Form;

use App\Entity\Ressource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class RessourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 3]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Formation' => 'FORMATION',
                    'Article' => 'ARTICLE',
                    'Vidéo' => 'VIDEO',
                    'Événement' => 'EVENT'
                ]
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Santé' => 'Santé',
                    'Bien-être' => 'Bien-être',
                    'Développement personnel' => 'Développement personnel'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['rows' => 5]
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur'
            ])
            ->add('dateCreation', DateType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text'
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Publié' => 'PUBLISHED',
                    'Brouillon' => 'DRAFT'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ressource::class,
        ]);
    }
}
