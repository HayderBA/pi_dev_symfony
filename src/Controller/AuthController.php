<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\SessionUserService;
use App\Entity\User;
use App\Entity\Patient;
use App\Entity\Doctor;

class AuthController extends AbstractController
{
    // ─────────────────────────────────────────
// REGISTER
// ─────────────────────────────────────────
#[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
public function register(Request $request, ManagerRegistry $doctrine): Response
{
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
        $role       = $request->request->get('role', 'patient');
        $phone      = trim($request->request->get('phone_number', ''));
        $age        = (int) $request->request->get('age', 0);
        $gender     = $request->request->get('gender', '');
        $birthDate  = $request->request->get('birth_date', '');

        // ✅ VALIDATION
        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Champs obligatoires.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords non identiques.';
        } elseif ($doctrine->getRepository(User::class)->findOneBy(['email' => $email])) {
            $error = 'Email déjà utilisé.';
        } else {

            $user = new User();
            $user->setName($name);
            $user->setSecondName($secondName);
            $user->setEmail($email);

            // 🔐 HASH PASSWORD (Symfony style)
            $user->setPassword(password_hash($password, PASSWORD_BCRYPT));

            // 🔥 ROLE CORRECT
            $user->setRole('ROLE_' . strtoupper($role));

            $user->setPhoneNumber($phone);
            $user->setAge($age);
            $user->setGender($gender);
            $user->setDtype($role);
            $user->setBirthDate($birthDate);

            $em->persist($user);

            // 👤 PATIENT
            if ($role === 'patient') {
                $patient = new Patient();
                $patient->setUser($user);
                $em->persist($patient);
            }

            // 👨‍⚕️ DOCTOR
            elseif ($role === 'doctor') {
                $doctor = new Doctor();
                $doctor->setUser($user);
                $em->persist($doctor);
            }

            $em->flush();

            return $this->redirectToRoute('app_login');
        }
    }

    return $this->render('auth/register.html.twig', [
        'error' => $error,
    ]);
}
}