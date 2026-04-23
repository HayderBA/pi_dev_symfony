<?php

namespace App\Form;

use App\Entity\SleepTracking;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SleepTrackingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => static fn (User $user): string => sprintf('%s (%s)', $user->getFullName(), $user->getEmail()),
                'label' => 'Utilisateur',
            ])
            ->add('dateSommeil', DateType::class, [
                'label' => 'Date du sommeil',
                'widget' => 'single_text',
            ])
            ->add('heureCoucher', TimeType::class, [
                'label' => 'Heure du coucher',
                'widget' => 'single_text',
            ])
            ->add('heureReveil', TimeType::class, [
                'label' => 'Heure du reveil',
                'widget' => 'single_text',
            ])
            ->add('dureeMinutes', IntegerType::class, [
                'label' => 'Duree en minutes',
                'attr' => ['min' => 0],
            ])
            ->add('qualiteSommeil', IntegerType::class, [
                'label' => 'Qualite du sommeil',
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SleepTracking::class,
        ]);
    }
}
