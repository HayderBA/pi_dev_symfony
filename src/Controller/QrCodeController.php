<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QrCodeController extends AbstractController
{
    #[Route('/2fa/setup', name: '2fa_setup', methods: ['GET'])]
    public function setup(
        GoogleAuthenticatorInterface $googleAuthenticator,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user || !$user->isPatient()) {
            return $this->redirectToRoute('app_home');
        }

        if (!$user->getGoogleAuthenticatorSecret()) {
            $user->setGoogleAuthenticatorSecret($googleAuthenticator->generateSecret());
            $entityManager->persist($user);
            $entityManager->flush();
        }

        $qrContent = $googleAuthenticator->getQRContent($user);
        $result = (new SvgWriter())->write(new QrCode($qrContent));

        return $this->render('security/qr_code.html.twig', [
            'qrCode' => $result->getString(),
            'secret' => $user->getGoogleAuthenticatorSecret(),
        ]);
    }

    #[Route('/2fa/setup/confirm', name: '2fa_setup_confirm', methods: ['GET'])]
    public function confirmSetup(): Response
    {
        $this->addFlash('success', '2FA configure avec succes. Reconnectez-vous pour valider le code.');

        return $this->redirectToRoute('app_logout');
    }
}
