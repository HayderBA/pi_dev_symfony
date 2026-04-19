<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // ✅ Si déjà TOTALEMENT authentifié (2FA compris) → redirection
        // On ne touche pas à la logique 2FA ici, Scheb s'en charge
        if ($this->getUser()) {
            return $this->redirectToRoute('after_login');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    // ✅ Point d'entrée après login réussi (et après 2FA validé par Scheb)
    #[Route('/after-login', name: 'after_login')]
    public function afterLogin(): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->getIsBlocked()) {
            return $this->redirectToRoute('app_logout');
        }

        // Patient sans secret → forcer setup QR
        if ($user->isPatient() && !$user->getGoogleAuthenticatorSecret()) {
            return $this->redirectToRoute('2fa_setup');
        }

        // Redirection selon rôle
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by firewall.');
    }
}