<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QrCodeController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────
    // SETUP 2FA  — appelé quand le patient n'a pas encore de secret
    //              (première connexion OU après reset admin)
    // ─────────────────────────────────────────────────────────────
    #[Route('/2fa/setup', name: '2fa_setup')]
    public function setup(
        GoogleAuthenticatorInterface $googleAuthenticator,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Sécurité : seuls les patients accèdent ici
        if (!$user || !$user->isPatient()) {
            return $this->redirectToRoute('app_home');
        }

        // ✅ Générer le secret UNE SEULE FOIS.
        //    Si un secret existe déjà (double appel, refresh), on ne le régénère pas.
        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $em->persist($user);
            $em->flush();
        }

        // Génération du QR code en SVG (pas de dépendance GD)
        $qrContent = $googleAuthenticator->getQRContent($user);
        $qrCode    = new QrCode($qrContent);
        $writer    = new SvgWriter();
        $result    = $writer->write($qrCode);
        $qrSvg     = $result->getString();

        return $this->render('security/qr_code.html.twig', [
            'qrCode' => $qrSvg,
            'secret' => $user->getGoogleAuthenticatorSecret(), // affiché en clair comme backup
        ]);
    }

  
    #[Route('/2fa/setup/confirm', name: '2fa_setup_confirm')]
    public function confirmSetup(): Response
    {
        // Après setup, on redirige vers le login pour un cycle propre.
        // L'user devra se reconnecter et passer le 2FA normalement.
        $this->addFlash('success', '2FA configuré avec succès ! Reconnectez-vous pour valider.');

        return $this->redirectToRoute('app_logout');
    }
}