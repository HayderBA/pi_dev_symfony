<?php

namespace App\Controller;

use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $em = $doctrine->getManager();

            $name       = trim($request->request->get('name', ''));
            $secondName = trim($request->request->get('second_name', ''));
            $email      = trim($request->request->get('email', ''));
            $password   = $request->request->get('password', '');
            $confirm    = $request->request->get('confirm_password', '');
            // ✅ Bug #1 fix : on garde 'patient'/'doctor' en minuscule, JAMAIS 'ROLE_*'
            $role       = $request->request->get('role', 'patient');
            $age        = (int) $request->request->get('age', 0);
            $gender     = $request->request->get('gender', '');
            $phone      = $request->request->get('phone_number', '');
            $birthDate  = $request->request->get('birth_date', '');

            // ── Validation ─────────────────────────────────────────
            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Les champs prénom, email et mot de passe sont obligatoires.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif (strlen($password) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caractères.';
            } elseif (!in_array($role, ['patient', 'doctor'])) {
                $error = 'Rôle invalide.';
            } elseif ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $error = 'Cet email est déjà utilisé.';
            } else {

                // ✅ Bug #3 fix : try/catch pour capturer toute erreur Doctrine
                try {
                    $user = new User();
                    $user->setName($name);
                    $user->setSecond_name($secondName);
                    $user->setEmail($email);

                    // ✅ Hash correct Symfony
                    $user->setPassword($passwordHasher->hashPassword($user, $password));

                    // ✅ Bug #1 fix : stocke 'patient' ou 'doctor' (minuscule)
                    //    getRoles() fait le match sur cette valeur → ROLE_PATIENT retourné
                    $user->setRole($role);
                    $user->setDtype($role);

                    // ✅ Bug #2 fix : on sette tous les champs nullable
                    //    pour éviter toute violation de contrainte
                    $user->setAge($age > 0 ? $age : 0);
                    $user->setGender($gender ?: null);
                    $user->setPhoneNumber($phone ? (int) $phone : null);
                    $user->setBirthDate($birthDate ?: null);
                    $user->setIsBlocked(false);

                    $em->persist($user);

                    if ($role === 'patient') {
                        $patient = new Patient();
                        $patient->setUser($user);
                        $patient->setBlood_type(
                            $request->request->get('blood_type') ?: null
                        );
                        $patient->setWeight(
                            $request->request->get('weight') !== ''
                                ? (float) $request->request->get('weight')
                                : null
                        );
                        $patient->setHeight(
                            $request->request->get('height') !== ''
                                ? (float) $request->request->get('height')
                                : null
                        );
                        $em->persist($patient);
                    } elseif ($role === 'doctor') {
                        $doctor = new Doctor();
                        $doctor->setUser($user);
                        $em->persist($doctor);
                    }

                    $em->flush();

                    $this->addFlash('success', 'Compte créé ! Connectez-vous.');
                    return $this->redirectToRoute('app_login');

                } catch (\Exception $e) {
                    // ✅ Bug #3 fix : affiche l'erreur réelle au lieu d'une page blanche
                    $error = 'Erreur lors de la création du compte : ' . $e->getMessage();
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'error' => $error,
        ]);
    }
}