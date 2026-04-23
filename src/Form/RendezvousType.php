<?php

namespace App\Form;

use App\Entity\Rendezvous;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezvousType extends AbstractType
{
    private const CTRL = ['class' => 'form-control'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['include_cabinet_choice']) {
            $builder->add('cabinet_id', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Cabinet',
                'placeholder' => 'Choisir un cabinet',
                'choices' => $options['cabinet_choices'],
                'data' => $options['selected_cabinet_id'],
                'attr' => self::CTRL,
            ]);
        }

        if ($options['include_psychologue_choice']) {
            $builder->add('psychologue_id', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Psychologue',
                'placeholder' => $options['selected_cabinet_id'] ? 'Choisir un psychologue' : 'Choisissez d abord un cabinet',
                'choices' => $options['psychologue_choices'],
                'data' => $options['selected_psychologue_id'],
                'attr' => self::CTRL,
            ]);
        }

        $builder
            ->add('dateRdv', DateType::class, ['label' => 'Date du rendez-vous', 'widget' => 'single_text', 'attr' => self::CTRL])
            ->add('heure', TimeType::class, ['label' => 'Heure', 'widget' => 'single_text', 'attr' => self::CTRL])
            ->add('typeCons', TextType::class, ['label' => 'Type de consultation', 'attr' => self::CTRL])
            ->add('nomPatient', TextType::class, ['label' => 'Nom', 'property_path' => 'nomPatient', 'attr' => self::CTRL])
            ->add('prenomPatient', TextType::class, ['label' => 'Prenom', 'property_path' => 'prenomPatient', 'attr' => self::CTRL])
            ->add('emailPatient', EmailType::class, ['label' => 'Email', 'property_path' => 'emailPatient', 'attr' => self::CTRL])
            ->add('telephonePatient', TextType::class, ['label' => 'Telephone', 'property_path' => 'telephonePatient', 'attr' => self::CTRL]);

        if ($options['include_statut']) {
            $builder->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => ['En attente' => 'en_attente', 'Confirme' => 'confirme', 'Annule' => 'annule'],
                'attr' => self::CTRL,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rendezvous::class,
            'include_statut' => false,
            'include_cabinet_choice' => false,
            'include_psychologue_choice' => false,
            'cabinet_choices' => [],
            'psychologue_choices' => [],
            'selected_cabinet_id' => null,
            'selected_psychologue_id' => null,
        ]);
    }
}
