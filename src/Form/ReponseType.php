<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('auteur', TextType::class, [
                'label' => 'Votre nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre nom ou pseudo']
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Votre réponse',
                'attr' => ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Écrivez votre réponse...']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}