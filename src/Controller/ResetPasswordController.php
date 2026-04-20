<?php
// src/Controller/ResetPasswordController.php

namespace App\Controller;

use App\Entity\ResetPasswordToken;
use App\Form\ForgotPasswordType;
use App\Form\NewPasswordType;
use App\Repository\ResetPasswordTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordTokenRepository $tokenRepository,
        private UserRepository               $userRepository,
        private EntityManagerInterface       $em,
        private UserPasswordHasherInterface  $passwordHasher,
        private MailerInterface              $mailer,
    ) {}

    #[Route('/mot-de-passe-oublie', name: 'forgot_password')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user  = $this->userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Supprime les anciens tokens
                $old = $this->tokenRepository->findBy(['user' => $user]);
                foreach ($old as $t) {
                    $this->em->remove($t);
                }

                // Crée le token
                $token = bin2hex(random_bytes(32));

                $resetToken = new ResetPasswordToken();
                $resetToken->setUser($user);
                $resetToken->setToken($token);
                $resetToken->setExpiresAt(new \DateTime('+1 hour'));

                $this->em->persist($resetToken);
                $this->em->flush();

                // Envoie l'email
                $email = (new TemplatedEmail())
                    ->from(new Address('noreply@tonsite.com', 'Mon App'))
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('emails/reset_password.html.twig')
                    ->context(['token' => $token, 'user' => $user]);

                $this->mailer->send($email);
            }

            $this->addFlash('success', 'Si cet email existe, un lien a été envoyé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reinitialiser/{token}', name: 'reset_password')]
    public function reset(string $token, Request $request): Response
    {
        $resetToken = $this->tokenRepository->findValidToken($token);

        if (!$resetToken) {
            $this->addFlash('error', 'Lien invalide ou expiré.');
            return $this->redirectToRoute('forgot_password');
        }

        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user        = $resetToken->getUser();
            $newPassword = $form->get('password')->getData();

            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $newPassword)
            );

            // Supprime le token
            $this->em->remove($resetToken);
            $this->em->flush();

            // Email confirmation
            $confirmEmail = (new TemplatedEmail())
                ->from(new Address('noreply@tonsite.com', 'Mon App'))
                ->to($user->getEmail())
                ->subject('Mot de passe modifié avec succès')
                ->htmlTemplate('emails/reset_password_confirm.html.twig')
                ->context(['user' => $user]);

            $this->mailer->send($confirmEmail);

            $this->addFlash('success', 'Mot de passe modifié avec succès !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}