<?php

namespace App\Form;

use App\Entity\Favori;
use App\Entity\Ressource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class FavoriType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userId', IntegerType::class, [
                'label' => 'ID Utilisateur'
            ])
            ->add('ressource', EntityType::class, [
                'class' => Ressource::class,
                'choice_label' => 'title',
                'label' => 'Ressource'
            ])
            ->add('dateAjout', DateType::class, [
                'label' => 'Date d\'ajout',
                'widget' => 'single_text'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Favori::class,
        ]);
    }
}
