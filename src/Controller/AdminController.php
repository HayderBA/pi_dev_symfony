<?php

namespace App\Controller;


use App\Entity\Admin;
use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    // =========================
    // 🏠 DASHBOARD ADMIN
    // =========================
#[Route('/admin_dash', name: 'app_admin_dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {
        // ✅ Vérifier connexion + rôle
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        $em = $doctrine->getManager();

        $users          = $em->getRepository(User::class)->findAll();
        $doctorEntities = $em->getRepository(Doctor::class)->findAll();

        // ✅ Filtrer par rôle
        $doctors  = array_values(array_filter($users, fn($u) => strtolower($u->getRole()) === 'doctor'));
        $patients = array_values(array_filter($users, fn($u) => strtolower($u->getRole()) === 'patient'));
        $admins   = array_values(array_filter($users, fn($u) => strtolower($u->getRole()) === 'admin'));

        // ✅ isBlocked → utiliser isBlocked() ou getIsBlocked() selon ton entity
        // Si ta propriété s'appelle $is_blocked → getter = isBlocked() ou getIsBlocked()
        $stats = [
            'totaluser'        => count($users),
            'totalDoctors'     => count($doctors),
            'totalAdmins'      => count($admins),
            'totalPatients'    => count($patients),
            'activeuser'       => count(array_filter($users,  fn($u) => !$u->getIs_blocked())),
            'blockeduser'      => count(array_filter($users,  fn($u) => $u->getIs_blocked())),
            'activeDoctors'    => count(array_filter($doctorEntities, fn($d) => $d->getActif())),
            'availableDoctors' => count(array_filter($doctorEntities, fn($d) => $d->getDisponible())),
        ];

        return $this->render('back/admin.html.twig', [
            'users'     => $users,
            'doctors'   => $doctors,
            'admins'    => $admins,
            'patients'  => $patients,
            'stats'     => $stats,
            'chartData' => [
                'doctors'  => $stats['totalDoctors'],
                'patients' => $stats['totalPatients'],
                'admins'   => $stats['totalAdmins'],
                'active'   => $stats['activeuser'],
                'blocked'  => $stats['blockeduser'],
            ],
        ]);
    }
    #[Route('/admin_base/add', name: 'admin_add_users', methods: ['GET', 'POST'])]
    public function add(
        Request $request,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher  // ✅ injection
    ): Response {
        $em   = $doctrine->getManager();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
 
            $role = $form->get('role')->getData(); // 'patient' | 'doctor' | 'admin'
 
            // ✅ Normalisation via le setter (gère 'patient' et 'ROLE_PATIENT')
            $user->setRole($role);
            $user->setDtype($role);
            $user->setIsBlocked(false);
 
            // ✅ Hash du mot de passe si le formulaire le contient
            if ($form->has('plainPassword')) {
                $plain = $form->get('plainPassword')->getData();
                if ($plain) {
                    $user->setPassword($passwordHasher->hashPassword($user, $plain));
                }
            }
 
            $em->persist($user);
            $em->flush(); // flush ici pour avoir l'ID avant les entités liées
 
            if ($role === 'patient') {
                $patient = new Patient();
                $patient->setUser($user);
                $patient->setBlood_type($form->get('blood_type')->getData());
                $patient->setWeight($form->get('weight')->getData());
                $patient->setHeight($form->get('height')->getData());
                $em->persist($patient);
            }
 
            if ($role === 'doctor') {
                $doctor = new Doctor();
                $doctor->setUser($user);
                $doctor->setSpecialty($form->get('specialty')->getData());
                $doctor->setExperience($form->get('experience')->getData());
                $doctor->setDiplome($form->get('diplome')->getData());
                $doctor->setDisponible($form->get('disponible')->getData());
                $doctor->setActif($form->get('actif')->getData());
                $doctor->setTarifConsultation($form->get('tarif_consultation')->getData());
                $em->persist($doctor);
            }
 
            $em->flush();
            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('app_admin_dashboard');
        }
 
        return $this->render('back/admin/add_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }
 
    // ─────────────────────────────────────────────────────────────
    // ✏️  EDIT USER
    // ─────────────────────────────────────────────────────────────
    #[Route('/admin_base/edit/{id}', name: 'admin_edit_user')]
    public function edit(
        int $id,
        Request $request,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $em   = $doctrine->getManager();
        $user = $em->getRepository(User::class)->find($id);
 
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }
 
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
 
            $role = $form->get('role')->getData();
            $user->setRole($role);
            $user->setDtype($role);
 
            // ✅ Rehash si le formulaire contient un nouveau mot de passe
            if ($form->has('plainPassword')) {
                $plain = $form->get('plainPassword')->getData();
                if ($plain) {
                    $user->setPassword($passwordHasher->hashPassword($user, $plain));
                }
            }
 
            if ($role === 'patient') {
                $patient = $em->getRepository(Patient::class)->findOneBy(['user' => $user])
                    ?? (new Patient())->setUser($user);
 
                $patient->setBlood_type($form->get('blood_type')->getData());
                $patient->setWeight($form->get('weight')->getData());
                $patient->setHeight($form->get('height')->getData());
                $em->persist($patient);
            }
 
            if ($role === 'doctor') {
                $doctor = $em->getRepository(Doctor::class)->findOneBy(['user' => $user])
                    ?? (new Doctor())->setUser($user);
 
                $doctor->setSpecialty($form->get('specialty')->getData());
                $doctor->setDiplome($form->get('diplome')->getData());
                $doctor->setExperience($form->get('experience')->getData());
                $doctor->setDisponible($form->get('disponible')->getData());
                $doctor->setActif($form->get('actif')->getData());
                $doctor->setTarifConsultation($form->get('tarif_consultation')->getData());
                $em->persist($doctor);
            }
 
            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('app_admin_dashboard');
        }
 
        return $this->render('back/admin/edit_user.html.twig', [
            'form'    => $form->createView(),
            'user'    => $user,
            'doctor'  => $em->getRepository(Doctor::class)->findOneBy(['user' => $user]),
            'patient' => $em->getRepository(Patient::class)->findOneBy(['user' => $user]),
        ]);
    }
 
    // ─────────────────────────────────────────────────────────────
    // 🗑️  DELETE USER
    // ─────────────────────────────────────────────────────────────
    #[Route('/admin_base/rm/{id}', name: 'admin_rm_users', methods: ['GET', 'POST'])]
    public function remove(int $id, ManagerRegistry $doctrine): Response
    {
        $em   = $doctrine->getManager();
        $user = $em->getRepository(User::class)->find($id);
 
        if (!$user) {
            $this->addFlash('error', "Utilisateur #$id introuvable.");
            return $this->redirectToRoute('app_admin_dashboard');
        }
 
        $patient = $em->getRepository(Patient::class)->findOneBy(['user' => $user]);
        if ($patient) {
            $em->remove($patient);
        }
 
        $name = $user->getName() . ' ' . $user->getSecond_name();
        $em->remove($user);
        $em->flush();
 
        $this->addFlash('success', "\"$name\" supprimé avec succès.");
        return $this->redirectToRoute('app_admin_dashboard');
    }
 
    // ─────────────────────────────────────────────────────────────
    // 🔒 TOGGLE BLOCK
    // ─────────────────────────────────────────────────────────────
    #[Route('/admin_base/toggle-block/{id}', name: 'admin_toggle_block', methods: ['GET', 'POST'])]
    public function toggleBlock(int $id, ManagerRegistry $doctrine): Response
    {
        $em   = $doctrine->getManager();
        $user = $em->getRepository(User::class)->find($id);
 
        if (!$user) {
            $this->addFlash('error', "Utilisateur #$id introuvable.");
            return $this->redirectToRoute('app_admin_dashboard');
        }
 
        // ✅ getIsBlocked() retourne bool → toggle propre
        $user->setIsBlocked(!$user->getIsBlocked());
        $em->flush();
 
        $this->addFlash(
            'success',
            'Utilisateur #' . $id . ($user->getIsBlocked() ? ' bloqué.' : ' débloqué.')
        );
 
        return $this->redirectToRoute('app_admin_dashboard');
    }
 
    // ─────────────────────────────────────────────────────────────
    // 🔄 RESET 2FA
    // ─────────────────────────────────────────────────────────────
    #[Route('/admin/reset-2fa/{id}', name: 'admin_reset_2fa')]
    public function reset2fa(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // ✅ Mettre NULL → patient devra rescanner au prochain login
        $user->setGoogleAuthenticatorSecret(null);
        $em->flush();

        $this->addFlash('success', '2FA réinitialisé pour ' . $user->getEmail());

        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/header_dash', name: 'admin_header_dash')]
    public function header_admin(): Response
    {
        return $this->render('back/side_header_admin.html.twig');
    }
    #[Route('/header_dash_doctor', name: 'admin_header_doc_dash')]
    public function header_doc_admin(): Response
    {
        return $this->render('back/side_header_doctor.html.twig');
    }
}