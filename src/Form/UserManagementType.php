<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Prenom'])
            ->add('secondName', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class)
            ->add('age', IntegerType::class, ['required' => false])
            ->add('gender', ChoiceType::class, [
                'required' => false,
                'choices' => ['Homme' => 'male', 'Femme' => 'female'],
            ])
            ->add('phoneNumber', IntegerType::class, [
                'required' => false,
                'label' => 'Numero de telephone',
            ])
            ->add('birthDate', TextType::class, [
                'required' => false,
                'label' => 'Date de naissance',
                'attr' => ['placeholder' => 'YYYY-MM-DD'],
            ])
            ->add('adresse', TextType::class, ['required' => false])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Patient' => 'patient',
                    'Medecin' => 'medecin',
                    'Admin' => 'admin',
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Nouveau mot de passe',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
