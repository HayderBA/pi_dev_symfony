<?php

namespace App\Form;

use App\Entity\Cabinet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CabinetType extends AbstractType
{
    private const CTRL = ['class' => 'form-control'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomcabinet', TextType::class, ['label' => 'Nom du cabinet', 'attr' => self::CTRL])
            ->add('adresse', TextType::class, ['label' => 'Adresse', 'attr' => self::CTRL])
            ->add('ville', TextType::class, ['label' => 'Ville', 'attr' => self::CTRL])
            ->add('telephone', TextType::class, ['label' => 'Telephone', 'attr' => self::CTRL])
            ->add('email', EmailType::class, ['label' => 'Email', 'attr' => self::CTRL])
            ->add('description', TextareaType::class, ['label' => 'Description', 'attr' => array_merge(self::CTRL, ['rows' => 4])])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => ['Actif' => 'actif', 'Inactif' => 'inactif'],
                'attr' => self::CTRL,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Cabinet::class]);
    }
}
