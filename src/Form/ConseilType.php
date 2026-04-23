<?php

namespace App\Form;

use App\Entity\Conseil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConseilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeEtat', TextType::class, [
                'label' => 'Type d etat',
                'attr' => ['placeholder' => 'Ex. stress, fatigue, anxiete'],
            ])
            ->add('niveau', IntegerType::class, [
                'label' => 'Niveau',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 10],
            ])
            ->add('categorie', TextType::class, [
                'label' => 'Categorie',
                'required' => false,
                'attr' => ['placeholder' => 'Sommeil, sante mentale, nutrition...'],
            ])
            ->add('conseil', TextareaType::class, [
                'label' => 'Conseil',
                'attr' => ['rows' => 5],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conseil::class,
        ]);
    }
}
