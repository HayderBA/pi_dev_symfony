<?php

namespace App\Form;

use App\Entity\Conseils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConseilsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeEtat', TextType::class, [
                'label' => 'Type état',
                'attr' => [
                    'placeholder' => 'Ex. Stress, anxiété, fatigue',
                ],
            ])
            ->add('niveau', IntegerType::class, [
                'label' => 'Niveau',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Valeur indicative',
                ],
            ])
            ->add('conseil', TextareaType::class, [
                'label' => 'Conseil',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Rédigez la recommandation à afficher',
                ],
            ])
            ->add('categorie', TextType::class, [
                'label' => 'Catégorie',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex. Santé mentale, sommeil, nutrition',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conseils::class,
        ]);
    }
}
