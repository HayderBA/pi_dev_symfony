<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserManagementType;
use App\Repository\EvaluationRepository;
use App\Repository\FavoriRepository;
use App\Repository\RessourceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/admin_dash', name: 'app_admin_dashboard')]
    public function index(UserRepository $userRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        $users = $userRepository->findBy([], ['id' => 'DESC']);
        $admins = array_values(array_filter($users, fn(User $user) => $user->isAdmin()));
        $doctors = array_values(array_filter($users, fn(User $user) => $user->isDoctor()));
        $patients = array_values(array_filter($users, fn(User $user) => $user->isPatient()));
        $blocked = count(array_filter($users, fn(User $user) => $user->getIsBlocked()));

        $userStats = [
            'total' => count($users),
            'admins' => count($admins),
            'doctors' => count($doctors),
            'patients' => count($patients),
            'blocked' => $blocked,
        ];

        return $this->render('back/admin.html.twig', [
            'managed_users' => $users,
            'user_stats' => $userStats,
            'users' => $users,
            'admins' => $admins,
            'doctors' => $doctors,
            'patients' => $patients,
            'stats' => [
                'totaluser' => $userStats['total'],
                'totalAdmins' => $userStats['admins'],
                'totalDoctors' => $userStats['doctors'],
                'totalPatients' => $userStats['patients'],
                'blockeduser' => $userStats['blocked'],
                'activeuser' => $userStats['total'] - $userStats['blocked'],
            ],
            'chartData' => [
                'admins' => $userStats['admins'],
                'doctors' => $userStats['doctors'],
                'patients' => $userStats['patients'],
                'blocked' => $userStats['blocked'],
                'active' => $userStats['total'] - $userStats['blocked'],
            ],
        ]);
    }

    #[Route('/doctor', name: 'app_doctor_dashboard')]
    public function doctor(): Response
    {
        return $this->render('back/doctor.html.twig');
    }

    #[Route('/patient', name: 'app_patient_dashboard')]
    public function patient(): Response
    {
        return $this->render('back/patient.html.twig');
    }

    #[Route('/patient/ressources', name: 'app_patient_ressources')]
    public function patientRessources(RessourceRepository $ressourceRepository, FavoriRepository $favoriRepository): Response
    {
        $userId = 1;
        $myFavoris = $favoriRepository->findBy(['userId' => $userId]);
        $favoriIds = array_map(fn($favori) => $favori->getRessource()->getId(), $myFavoris);

        return $this->render('back/patient/ressources.html.twig', [
            'ressources' => $ressourceRepository->findBy(['status' => 'PUBLISHED']),
            'favoriIds' => $favoriIds,
        ]);
    }

    #[Route('/patient/mes-avis', name: 'app_patient_evaluations')]
    public function patientEvaluations(EvaluationRepository $evaluationRepository): Response
    {
        $userId = 1;

        return $this->render('back/patient/evaluations.html.twig', [
            'my_evaluations' => $evaluationRepository->findBy(['userId' => $userId]),
        ]);
    }

    #[Route('/calendar-events', name: 'app_admin_calendar_events')]
    public function calendarEvents(RessourceRepository $ressourceRepository, EvaluationRepository $evaluationRepository): JsonResponse
    {
        $events = [];

        foreach ($ressourceRepository->findAll() as $ressource) {
            if ($ressource->getDateCreation()) {
                $events[] = [
                    'title' => 'Document ' . $ressource->getTitle(),
                    'start' => $ressource->getDateCreation()->format('Y-m-d'),
                    'color' => '#1e88e5',
                    'url' => '/ressource/' . $ressource->getId(),
                ];
            }
        }

        foreach ($evaluationRepository->findAll() as $evaluation) {
            if ($evaluation->getDateEvaluation()) {
                $events[] = [
                    'title' => ($evaluation->getRessource() ? $evaluation->getRessource()->getTitle() : 'Evaluation') . ' (' . $evaluation->getNote() . '/5)',
                    'start' => $evaluation->getDateEvaluation()->format('Y-m-d'),
                    'color' => '#f59e0b',
                    'url' => '/evaluation/' . $evaluation->getId(),
                ];
            }
        }

        return new JsonResponse($events);
    }

    #[Route('/users/add', name: 'admin_add_users', methods: ['GET', 'POST'])]
    public function addUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserManagementType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();

            if ('' === $plainPassword) {
                $this->addFlash('error', 'Le mot de passe est obligatoire.');
            } elseif ($entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()])) {
                $this->addFlash('error', 'Cet email existe deja.');
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
                $user->setIsBlocked(false);
                $user->setIsVerified(true);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Utilisateur cree avec succes.');

                return $this->redirectToRoute('app_admin_dashboard');
            }
        }

        return $this->render('back/admin/add_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/edit/{id}', name: 'admin_edit_user', methods: ['GET', 'POST'])]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserManagementType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();

            if ('' !== $plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur modifie avec succes.');

            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('back/admin/edit_user.html.twig', [
            'form' => $form->createView(),
            'managed_user' => $user,
        ]);
    }

    #[Route('/users/delete/{id}', name: 'admin_rm_users', methods: ['POST'])]
    public function removeUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprime avec succes.');
        }

        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/users/toggle-block/{id}', name: 'admin_toggle_block', methods: ['POST'])]
    public function toggleBlock(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_user_' . $user->getId(), (string) $request->request->get('_token'))) {
            $user->setIsBlocked(!$user->getIsBlocked());
            $entityManager->flush();
            $this->addFlash('success', $user->getIsBlocked() ? 'Utilisateur bloque.' : 'Utilisateur debloque.');
        }

        return $this->redirectToRoute('app_admin_dashboard');
    }
}
