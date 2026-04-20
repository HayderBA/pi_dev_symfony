<?php

namespace App\Form;

use App\Entity\Evaluation;
use App\Entity\Ressource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', ChoiceType::class, [
                'label' => 'Note (sur 5)',
                'choices' => [
                    '5 - Excellent' => 5,
                    '4 - Très bien' => 4,
                    '3 - Bien' => 3,
                    '2 - Moyen' => 2,
                    '1 - Mauvais' => 1,
                ],
                'attr' => ['class' => 'gm-input']
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => ['rows' => 3, 'class' => 'gm-input', 'placeholder' => 'Votre avis...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evaluation::class,
            'allow_extra_fields' => true,
        ]);
    }
}
