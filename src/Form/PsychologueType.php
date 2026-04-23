<?php

namespace App\Form;

use App\Entity\Cabinet;
use App\Entity\Psychologue;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PsychologueType extends AbstractType
{
    private const CTRL = ['class' => 'form-control'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cabinet', EntityType::class, [
                'class' => Cabinet::class,
                'choice_label' => 'nomcabinet',
                'label' => 'Cabinet',
                'placeholder' => 'Choisir un cabinet',
                'attr' => self::CTRL,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => static fn (User $user) => $user->getFullName() . ' - ' . $user->getEmail(),
                'query_builder' => static fn (UserRepository $repo) => $repo->createQueryBuilder('u')
                    ->andWhere('LOWER(u.role) IN (:roles)')
                    ->setParameter('roles', ['medecin', 'doctor'])
                    ->orderBy('u.name', 'ASC')
                    ->addOrderBy('u.secondName', 'ASC'),
                'required' => false,
                'placeholder' => 'Lier un medecin depuis users',
                'label' => 'Medecin lie',
                'attr' => self::CTRL,
            ])
            ->add('nom', TextType::class, ['label' => 'Nom', 'attr' => self::CTRL])
            ->add('prenom', TextType::class, ['label' => 'Prenom', 'attr' => self::CTRL])
            ->add('specialite', TextType::class, ['label' => 'Specialite', 'attr' => self::CTRL])
            ->add('diplome', TextType::class, ['label' => 'Diplome', 'attr' => self::CTRL])
            ->add('experience', IntegerType::class, ['label' => 'Experience (ans)', 'attr' => self::CTRL])
            ->add('tarif', MoneyType::class, ['label' => 'Tarif', 'currency' => 'TND', 'attr' => self::CTRL])
            ->add('email', TextType::class, ['label' => 'Email', 'attr' => self::CTRL])
            ->add('telephone', TextType::class, ['label' => 'Telephone', 'attr' => self::CTRL]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Psychologue::class]);
    }
}
