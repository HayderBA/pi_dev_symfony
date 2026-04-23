<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $secondName = trim((string) $request->request->get('second_name', ''));
            $email = trim((string) $request->request->get('email', ''));
            $password = (string) $request->request->get('password', '');
            $confirm = (string) $request->request->get('confirm_password', '');
            $role = strtolower((string) $request->request->get('role', 'patient'));
            $age = (int) $request->request->get('age', 0);
            $gender = (string) $request->request->get('gender', '');
            $phone = (string) $request->request->get('phone_number', '');
            $birthDate = (string) $request->request->get('birth_date', '');

            if ('' === $name || '' === $email || '' === $password) {
                $error = 'Les champs prenom, email et mot de passe sont obligatoires.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif (strlen($password) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caracteres.';
            } elseif (!in_array($role, ['patient', 'doctor'], true)) {
                $error = 'Role invalide.';
            } elseif ($entityManager->getRepository(User::class)->findOneBy(['email' => $email])) {
                $error = 'Cet email est deja utilise.';
            } else {
                try {
                    $user = new User();
                    $user->setName($name);
                    $user->setSecondName($secondName);
                    $user->setEmail($email);
                    $user->setPassword($passwordHasher->hashPassword($user, $password));
                    $user->setRole($role);
                    $user->setAge($age > 0 ? $age : null);
                    $user->setGender('' !== $gender ? $gender : null);
                    $user->setPhoneNumber('' !== $phone ? (int) $phone : null);
                    $user->setBirthDate('' !== $birthDate ? $birthDate : null);
                    $user->setIsBlocked(false);
                    $user->setIsVerified(true);

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Compte cree ! Connectez-vous.');

                    return $this->redirectToRoute('app_login');
                } catch (\Throwable $exception) {
                    $error = 'Erreur lors de la creation du compte : ' . $exception->getMessage();
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'error' => $error,
        ]);
    }
}
