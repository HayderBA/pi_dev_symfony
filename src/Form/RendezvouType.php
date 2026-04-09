<?php

namespace App\Form;

use App\Entity\Rendezvou;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezvouType extends AbstractType
{
    private const CTRL = ['class' => 'form-control'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['include_cabinet_choice']) {
            $builder->add('cabinet_id', ChoiceType::class, [
                'label' => 'Cabinet',
                'mapped' => false,
                'required' => true,
                'placeholder' => 'Choisir un cabinet',
                'choices' => $options['cabinet_choices'],
                'data' => $options['selected_cabinet_id'],
                'attr' => self::CTRL,
            ]);
        }

        if ($options['include_psychologue_choice']) {
            $builder->add('idPsychologue', ChoiceType::class, [
                'label' => 'Psychologue',
                'required' => true,
                'placeholder' => $options['selected_cabinet_id'] ? 'Choisir un psychologue' : 'Choisissez d’abord un cabinet',
                'choices' => $options['psychologue_choices'],
                'attr' => self::CTRL,
            ]);
        }

        $builder
            ->add('dateRdv', DateType::class, [
                'label' => 'Date du rendez-vous',
                'widget' => 'single_text',
                'attr' => self::CTRL,
            ])
            ->add('heure', TimeType::class, [
                'label' => 'Heure',
                'widget' => 'single_text',
                'attr' => self::CTRL,
            ])
            ->add('typeCons', TextType::class, [
                'label' => 'Type de consultation',
                'attr' => self::CTRL,
            ])
            ->add('nom_patient', TextType::class, [
                'label' => 'Nom',
                'attr' => self::CTRL,
            ])
            ->add('prenom_patient', TextType::class, [
                'label' => 'Prénom',
                'attr' => self::CTRL,
            ])
            ->add('email_patient', EmailType::class, [
                'label' => 'Email',
                'attr' => self::CTRL,
            ])
            ->add('telephone_patient', TextType::class, [
                'label' => 'Téléphone',
                'attr' => self::CTRL,
            ]);

        if ($options['include_statut']) {
            $builder->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'en_attente',
                    'Confirmé' => 'confirme',
                    'Annulé' => 'annule',
                ],
                'attr' => self::CTRL,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rendezvou::class,
            'include_statut' => false,
            'include_cabinet_choice' => false,
            'include_psychologue_choice' => false,
            'cabinet_choices' => [],
            'psychologue_choices' => [],
            'selected_cabinet_id' => null,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
        $resolver->setAllowedTypes('include_statut', 'bool');
        $resolver->setAllowedTypes('include_cabinet_choice', 'bool');
        $resolver->setAllowedTypes('include_psychologue_choice', 'bool');
        $resolver->setAllowedTypes('cabinet_choices', 'array');
        $resolver->setAllowedTypes('psychologue_choices', 'array');
        $resolver->setAllowedTypes('selected_cabinet_id', ['null', 'int']);
    }
}
