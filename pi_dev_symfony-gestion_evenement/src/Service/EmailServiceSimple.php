<?php

namespace App\Service;

use App\Entity\Reservation;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * ✅ SERVICE D'EMAILS SIMPLE ET STABLE - SANS TEMPLATES TWIG EXTERNE
 * Utilise directement Email() de Symfony - fonctionne toujours
 */
class EmailServiceSimple
{
    public function __construct(private MailerInterface $mailer) {}

    /**
     * Confirmation de réservation - avec QR code
     */
    public function sendReservationConfirmation(Reservation $reservation): void
    {
        try {
            $event = $reservation->getEvenement();
            $html = $this->renderConfirmation($reservation, $event);
            
            $email = (new Email())
                ->from('noreply@growmind.com')
                ->to($reservation->getEmail())
                ->subject('🎫 Votre billet: ' . $event->getTitre())
                ->html($html);
            
            $this->mailer->send($email);
            echo "[EMAIL] ✅ Confirmation envoyé à " . $reservation->getEmail() . "\n";
        } catch (\Exception $e) {
            echo "[EMAIL ERROR] " . $e->getMessage() . "\n";
        }
    }

    /**
     * Email de bienvenue
     */
    public function sendWelcomeEmail(Reservation $reservation): void
    {
        try {
            $nom = htmlspecialchars($reservation->getNom());
            $html = <<<HTML
            <html style="font-family: Arial;">
            <body>
                <h1 style="color: #6BBF59;">✨ Bienvenue sur GrowMind!</h1>
                <p>Bonjour <b>$nom</b>,</p>
                <p>Merci d'avoir rejoins notre communauté d'événements!</p>
                <p>Vous recevrez vos billets par email quelques minutes après votre réservation.</p>
                <p style="margin-top: 30px;">À bientôt! 🎉</p>
            </body>
            </html>
            HTML;
            
            $email = (new Email())
                ->from('noreply@growmind.com')
                ->to($reservation->getEmail())
                ->subject('✨ Bienvenue à GrowMind!')
                ->html($html);
            
            $this->mailer->send($email);
            echo "[EMAIL] ✅ Welcome envoyé à " . $reservation->getEmail() . "\n";
        } catch (\Exception $e) {
            echo "[EMAIL ERROR] " . $e->getMessage() . "\n";
        }
    }

    /**
     * Rappel 24h avant l'événement
     */
    public function sendReminderEmail(Reservation $reservation): void
    {
        try {
            $event = $reservation->getEvenement();
            $nom = htmlspecialchars($reservation->getNom());
            $titre = htmlspecialchars($event->getTitre());
            $lieu = htmlspecialchars($event->getLocalisation());
            $date = $event->getDate()->format('d/m/Y à H:i');
            $siege = $reservation->getSeatNumber() ?? 'Non attribué';
            
            $html = <<<HTML
            <html style="font-family: Arial;">
            <body>
                <h1 style="color: #FF6B6B;">⏰ Demain: $titre</h1>
                <p>Bonjour <b>$nom</b>,</p>
                <p><strong style="font-size: 16px; color: #FF6B6B;">⚠️ Votre événement a lieu DEMAIN!</strong></p>
                
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #FF6B6B;">
                    <p><strong>📍 Lieu:</strong> $lieu</p>
                    <p><strong>🕐 Heure:</strong> $date</p>
                    <p><strong>🎫 Siège:</strong> $siege</p>
                    <p style="color: #666; margin: 10px 0 0 0;">💡 Arrivez 15 minutes avant le début!</p>
                </div>
                
                <p>À bientôt! 🎉</p>
            </body>
            </html>
            HTML;
            
            $email = (new Email())
                ->from('noreply@growmind.com')
                ->to($reservation->getEmail())
                ->subject('⏰ Demain: ' . $titre)
                ->html($html);
            
            $this->mailer->send($email);
            echo "[EMAIL] ✅ Reminder envoyé à " . $reservation->getEmail() . "\n";
        } catch (\Exception $e) {
            echo "[EMAIL ERROR] " . $e->getMessage() . "\n";
        }
    }

    /**
     * Sondage satisfaction 24h après
     */
    public function sendSatisfactionEmail(Reservation $reservation): void
    {
        try {
            $event = $reservation->getEvenement();
            $nom = htmlspecialchars($reservation->getNom());
            $titre = htmlspecialchars($event->getTitre());
            
            $html = <<<HTML
            <html style="font-family: Arial;">
            <body>
                <h1 style="color: #6BBF59;">📝 Votre avis nous intéresse!</h1>
                <p>Bonjour <b>$nom</b>,</p>
                <p>Merci d'avoir assisté à <strong>$titre</strong>!</p>
                <p>Nous aimerions connaître votre expérience. Cela nous aide à améliorer nos prochains événements.</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="#" style="background: #6BBF59; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px;">
                        📋 Donner mon avis
                    </a>
                </div>
                
                <p style="color: #999; font-size: 12px;">Merci pour votre participation!</p>
            </body>
            </html>
            HTML;
            
            $email = (new Email())
                ->from('noreply@growmind.com')
                ->to($reservation->getEmail())
                ->subject('📝 Votre avis sur ' . $event->getTitre())
                ->html($html);
            
            $this->mailer->send($email);
            echo "[EMAIL] ✅ Satisfaction envoyé à " . $reservation->getEmail() . "\n";
        } catch (\Exception $e) {
            echo "[EMAIL ERROR] " . $e->getMessage() . "\n";
        }
    }

    /**
     * Offre spéciale 20%
     */
    public function sendSpecialOfferEmail(Reservation $reservation): void
    {
        try {
            $nom = htmlspecialchars($reservation->getNom());
            $code = 'PROMO20_' . strtoupper(substr(md5($reservation->getEmail()), 0, 6));
            
            $html = <<<HTML
            <html style="font-family: Arial;">
            <body>
                <h1 style="color: #FFD700;">🎁 Offre Spéciale: 20% de réduction!</h1>
                <p>Bonjour <b>$nom</b>,</p>
                <p>En récompense de votre fidélité, voici un <strong>code promo exclusif</strong>:</p>
                
                <div style="background: #FFD700; padding: 20px; text-align: center; border-radius: 5px; margin: 20px 0;">
                    <p style="font-size: 28px; font-weight: bold; color: #333; margin: 0;">$code</p>
                </div>
                
                <div style="background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p>✅ <b>20% de réduction</b> sur votre prochain événement</p>
                    <p>⏱️ <b>Valide 30 jours</b> à partir d'aujourd'hui</p>
                    <p>🔗 Non transférable et usage personnel</p>
                </div>
                
                <p style="text-align: center;">
                    <a href="#" style="background: #6BBF59; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Voir les prochains événements
                    </a>
                </p>
            </body>
            </html>
            HTML;
            
            $email = (new Email())
                ->from('noreply@growmind.com')
                ->to($reservation->getEmail())
                ->subject('🎁 Code promo 20% - GrowMind')
                ->html($html);
            
            $this->mailer->send($email);
            echo "[EMAIL] ✅ Special offer envoyé à " . $reservation->getEmail() . "\n";
        } catch (\Exception $e) {
            echo "[EMAIL ERROR] " . $e->getMessage() . "\n";
        }
    }

    /**
     * Email personnalisé avec template simple
     */
    public function sendCustomEmail(string $to, string $subject, string $html): void
    {
        try {
            $email = (new Email())
                ->from('noreply@growmind.com')
                ->to($to)
                ->subject($subject)
                ->html($html);
            
            $this->mailer->send($email);
            echo "[EMAIL] ✅ Custom email envoyé à $to\n";
        } catch (\Exception $e) {
            echo "[EMAIL ERROR] " . $e->getMessage() . "\n";
        }
    }

    /**
     * Rendu HTML pour confirmation
     */
    private function renderConfirmation(Reservation $reservation, $event): string
    {
        $nom = htmlspecialchars($reservation->getNom());
        $titre = htmlspecialchars($event->getTitre());
        $date = $event->getDate()->format('d/m/Y à H:i');
        $lieu = htmlspecialchars($event->getLocalisation());
        $siege = $reservation->getSeatNumber() ?? 'Non attribué';
        $prix = $event->getDynamicPrice() ?? $event->getPrix();
        $qr = $reservation->getQrCode() ? 'QR Code généré ✅' : 'QR Code en attente...';
        
        return <<<HTML
        <html style="font-family: Arial;">
        <body style="line-height: 1.6;">
            <h1 style="color: #6BBF59;">✅ Réservation Confirmée!</h1>
            <p>Bonjour <b>$nom</b>,</p>
            <p>Votre réservation pour <strong>$titre</strong> a été confirmée avec succès! 🎉</p>
            
            <div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #6BBF59;">
                <h3 style="color: #333; margin-top: 0;">📋 Détails de votre réservation:</h3>
                <p><strong>Événement:</strong> $titre</p>
                <p><strong>📅 Date:</strong> $date</p>
                <p><strong>📍 Lieu:</strong> $lieu</p>
                <p><strong>🪑 Siège:</strong> $siege</p>
                <p><strong>💰 Montant:</strong> ${prix}</p>
                <p><strong>🎫 Billet:</strong> $qr</p>
            </div>
            
            <p style="color: #666; margin-top: 30px; font-size: 12px;">
                © 2025 GrowMind - Gestion d'événements
            </p>
        </body>
        </html>
        HTML;
    }
}
