<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('after_login');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/after-login', name: 'after_login')]
    public function afterLogin(EntityManagerInterface $entityManager): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->getIsBlocked()) {
            $this->addFlash('error', 'Votre compte est bloque.');

            return $this->redirectToRoute('app_logout');
        }

        if ($user->isPatient() && !$user->getGoogleAuthenticatorSecret()) {
            return $this->redirectToRoute('2fa_setup');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('face_page');
        }

        if ($user->isPatient()) {
            $row = $entityManager->getConnection()->fetchAssociative(
                'SELECT checked_at FROM mental_health_check WHERE patient_id = ? ORDER BY checked_at DESC LIMIT 1',
                [$user->getId()]
            );

            $needsCheck = true;

            if ($row) {
                $lastChecked = new \DateTime((string) $row['checked_at']);
                $now = new \DateTime();
                $diff = $now->diff($lastChecked);
                $hoursDiff = ($diff->days * 24) + $diff->h;

                if ($hoursDiff < 24) {
                    $needsCheck = false;
                }
            }

            if ($needsCheck) {
                return $this->redirectToRoute('mental_health_page');
            }
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Handled by the firewall.');
    }
}
