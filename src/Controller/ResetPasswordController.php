<?php

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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/mot-de-passe-oublie', name: 'forgot_password', methods: ['GET', 'POST'])]
    public function request(
        Request $request,
        UserRepository $userRepository,
        ResetPasswordTokenRepository $tokenRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailValue = (string) $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $emailValue]);

            if ($user) {
                foreach ($tokenRepository->findBy(['user' => $user]) as $oldToken) {
                    $entityManager->remove($oldToken);
                }

                $tokenValue = bin2hex(random_bytes(32));
                $resetToken = new ResetPasswordToken();
                $resetToken->setUser($user);
                $resetToken->setToken($tokenValue);
                $resetToken->setExpiresAt(new \DateTime('+1 hour'));
                $entityManager->persist($resetToken);
                $entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from('noreply@growmind.com')
                    ->to((string) $user->getEmail())
                    ->subject('Reinitialisation de votre mot de passe')
                    ->htmlTemplate('emails/reset_password.html.twig')
                    ->context([
                        'token' => $tokenValue,
                        'user' => $user,
                    ]);

                $mailer->send($email);
            }

            $this->addFlash('success', 'Si cet email existe, un lien de reinitialisation a ete envoye.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reinitialiser/{token}', name: 'reset_password', methods: ['GET', 'POST'])]
    public function reset(
        string $token,
        Request $request,
        ResetPasswordTokenRepository $tokenRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
    ): Response {
        $resetToken = $tokenRepository->findValidToken($token);

        if (!$resetToken) {
            $this->addFlash('error', 'Lien invalide ou expire.');

            return $this->redirectToRoute('forgot_password');
        }

        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = (string) $form->get('password')->getData();
            $confirm = (string) $form->get('confirm_password')->getData();

            if ($password !== $confirm) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            } else {
                $user = $resetToken->getUser();
                $user?->setPassword($passwordHasher->hashPassword($user, $password));
                $entityManager->remove($resetToken);
                $entityManager->flush();

                if ($user) {
                    $confirmEmail = (new TemplatedEmail())
                        ->from('noreply@growmind.com')
                        ->to((string) $user->getEmail())
                        ->subject('Mot de passe modifie')
                        ->htmlTemplate('emails/reset_password_confirm.html.twig')
                        ->context(['user' => $user]);

                    $mailer->send($confirmEmail);
                }

                $this->addFlash('success', 'Mot de passe modifie avec succes.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
