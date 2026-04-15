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

#[Route('/admin')]
class AdminController extends AbstractController
{
    // =========================
    // 🏠 DASHBOARD ADMIN
    // =========================
#[Route('/admin_dash', name: 'app_admin_dashboard')]
public function index(ManagerRegistry $doctrine): Response
{
    $em = $doctrine->getManager();

    // ✅ récupérer users
    $users = $em->getRepository(User::class)->findAll();

    // ✅ filtrer par role
    $doctors  = array_filter($users, fn($u) => strtolower($u->getRole()) === 'doctor');
    $patients = array_filter($users, fn($u) => strtolower($u->getRole()) === 'patient');
    $admins   = array_filter($users, fn($u) => strtolower($u->getRole()) === 'admin');

    // 🔥 récupérer les vraies entités Doctor
    $doctorEntities = $em->getRepository(Doctor::class)->findAll();

    $stats = [
        'totaluser'        => count($users),
        'totalDoctors'     => count($doctors),
        'totalAdmins'      => count($admins),
        'totalPatients'    => count($patients),

        'activeuser'       => count(array_filter($users, fn($u) => !$u->getIs_blocked())),
        'blockeduser'      => count(array_filter($users, fn($u) => $u->getIs_blocked())),

        // ✅ CORRECTION ICI
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
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {
        $em   = $doctrine->getManager();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $role = $form->get('role')->getData();

            // ✅ dtype
            if ($role === 'doctor') {
                $user->setDtype('doctor');
            } elseif ($role === 'patient') {
                $user->setDtype('patient');
            } else {
                $user->setDtype('admin');
            }

            // ✅ date
            $date = $user->getBirthDate();
            if ($date instanceof \DateTimeInterface) {
                $user->setBirthDate($date->format('Y-m-d'));
            }

            // 🔥 enregistrer user (génère ID)
            $em->persist($user);
            $em->flush();

            // 🔥 CREATE PATIENT
            if ($role === 'patient') {
                $patient = new Patient();
                $patient->setUser($user);
                $patient->setBlood_type($form->get('blood_type')->getData());
                $patient->setWeight($form->get('weight')->getData());
                $patient->setHeight($form->get('height')->getData());

                $em->persist($patient);
            }

            // 🔥 CREATE DOCTOR
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

            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('back/admin/add_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin_base/edit/{id}', name: 'admin_edit_user')]
        public function edit(int $id, Request $request, ManagerRegistry $doctrine): Response
        {
            $em = $doctrine->getManager();

            $user = $em->getRepository(User::class)->find($id);

            if (!$user) {
                throw $this->createNotFoundException('User not found');
            }

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $role = $form->get('role')->getData();
                $user->setDtype($role);

                $date = $user->getBirthDate();
                if ($date instanceof \DateTimeInterface) {
                    $user->setBirthDate($date->format('Y-m-d'));
                }

                if ($role === 'patient') {
                    $patient = $em->getRepository(Patient::class)->findOneBy(['user' => $user]);

                    if (!$patient) {
                        $patient = new Patient();
                        $patient->setUser($user);
                    }

                    $patient->setBlood_type($form->get('blood_type')->getData());
                    $patient->setWeight($form->get('weight')->getData());
                    $patient->setHeight($form->get('height')->getData());
                    $em->persist($patient);
                }

                if ($role === 'doctor') {
                    $doctor = $em->getRepository(Doctor::class)->findOneBy(['user' => $user]);

                    if (!$doctor) {
                        $doctor = new Doctor();
                        $doctor->setUser($user);
                    }

                    $doctor->setSpecialty($form->get('specialty')->getData());
                    $doctor->setDiplome($form->get('diplome')->getData());
                    $doctor->setExperience($form->get('experience')->getData());
                    $doctor->setDisponible($form->get('disponible')->getData());
                    $doctor->setActif($form->get('actif')->getData());
                    $doctor->setTarifConsultation($form->get('tarif_consultation')->getData());

                    $em->persist($doctor);
                }

                $em->flush();

                return $this->redirectToRoute('app_admin_dashboard');
            }

            return $this->render('back/admin/edit_user.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
                'doctor'  => $em->getRepository(Doctor::class)->findOneBy(['user' => $user]),
                'patient' => $em->getRepository(Patient::class)->findOneBy(['user' => $user]),
            ]);
        }

     #[Route('/admin_base/rm/{id}', name: 'admin_rm_users', methods: ['GET', 'POST'])]
    public function remove(int $id, ManagerRegistry $doctrine): Response
    {
        $em   = $doctrine->getManager();
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('error', "User #$id not found.");
            return $this->redirectToRoute('app_admin_dashboard');
        }

        $name = $user->getName() . ' ' . $user->getSecond_name();
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', "\"$name\" deleted from all tables.");
        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/admin_base/toggle-block/{id}', name: 'admin_toggle_block', methods: ['GET', 'POST'])]
    public function toggleBlock(int $id, ManagerRegistry $doctrine): Response
    {
        $em   = $doctrine->getManager();
        $user = $em->getRepository(User::class)->find($id);

        if ($user) {
            $user->setIsBlocked(!$user->getIsBlocked()); // ✅ CORRECT
            $em->flush();

            $this->addFlash(
                'success',
                'User #' . $id . ($user->getIsBlocked() ? ' blocked.' : ' unblocked.')
            );
        }

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
    
    
    // =========================
    // 👨‍⚕️ DASHBOARD DOCTOR
    // =========================
    #[Route('/doctor', name: 'app_doctor_dashboard')]
    public function doctor(): Response
    {
        return $this->render('back/doctor.html.twig');
    }

}