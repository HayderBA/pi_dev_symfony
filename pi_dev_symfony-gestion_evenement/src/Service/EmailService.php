<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ReservationRepository $reservationRepository,
        private readonly Environment $twig,
        #[Autowire(env: 'MAILER_FROM')]
        private readonly string $fromEmail = 'noreply@growmind.com'
    ) {
    }

    public function sendReservationConfirmation(Reservation $reservation): void
    {
        try {
            $event = $reservation->getEvenement();
            if (!$event) {
                return;
            }

            $html = $this->twig->render('emails/reservation_confirmation.html.twig', [
                'reservation' => $reservation,
            ]);

            $email = (new Email())
                ->from($this->fromEmail)
                ->to((string) $reservation->getEmail())
                ->subject('Votre billet GrowMind - ' . $event->getTitre())
                ->html($html);

            $this->mailer->send($email);

            if ($this->isFirstReservation($reservation)) {
                $this->sendWelcomeEmail($reservation);
            }
        } catch (\Throwable $e) {
            error_log('[EmailService] confirmation: ' . $e->getMessage());
        }
    }

    public function sendWelcomeEmail(Reservation $reservation): void
    {
        try {
            $event = $reservation->getEvenement();
            if (!$event) {
                return;
            }

            $html = $this->twig->render('emails/welcome.html.twig', [
                'clientName' => $reservation->getNom(),
                'event' => $event,
            ]);

            $email = (new Email())
                ->from($this->fromEmail)
                ->to((string) $reservation->getEmail())
                ->subject('Bienvenue sur GrowMind')
                ->html($html);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            error_log('[EmailService] welcome: ' . $e->getMessage());
        }
    }

    public function sendReminderEmail(Reservation $reservation): void
    {
        try {
            $event = $reservation->getEvenement();
            if (!$event) {
                return;
            }

            $now = new \DateTimeImmutable();
            $eventDate = \DateTimeImmutable::createFromInterface($event->getDate());
            $hours = max(1, (int) ceil(($eventDate->getTimestamp() - $now->getTimestamp()) / 3600));

            $html = $this->twig->render('emails/reminder_before_event.html.twig', [
                'reservation' => $reservation,
                'event' => $event,
                'qrCode' => $reservation->getQrCode(),
                'timeUntilEvent' => $hours . ' heure(s)',
            ]);

            $email = (new Email())
                ->from($this->fromEmail)
                ->to((string) $reservation->getEmail())
                ->subject('Rappel GrowMind - ' . $event->getTitre())
                ->html($html);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            error_log('[EmailService] reminder: ' . $e->getMessage());
        }
    }

    public function sendSatisfactionEmail(Reservation $reservation): void
    {
        try {
            $event = $reservation->getEvenement();
            if (!$event) {
                return;
            }

            $html = $this->twig->render('emails/satisfaction_after_event.html.twig', [
                'clientName' => $reservation->getNom(),
                'event' => $event,
            ]);

            $email = (new Email())
                ->from($this->fromEmail)
                ->to((string) $reservation->getEmail())
                ->subject('Votre avis sur ' . $event->getTitre())
                ->html($html);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            error_log('[EmailService] satisfaction: ' . $e->getMessage());
        }
    }

    public function sendSpecialOfferEmail(Reservation $reservation): void
    {
        try {
            $discountCode = 'GROW20-' . strtoupper(substr(md5((string) $reservation->getEmail()), 0, 6));

            $html = $this->twig->render('emails/special_offer.html.twig', [
                'clientName' => $reservation->getNom(),
                'discountCode' => $discountCode,
            ]);

            $email = (new Email())
                ->from($this->fromEmail)
                ->to((string) $reservation->getEmail())
                ->subject('20% de reduction sur votre prochain evenement')
                ->html($html);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            error_log('[EmailService] offer: ' . $e->getMessage());
        }
    }

    public function sendCustomEmail(string $to, string $subject, string $html): void
    {
        try {
            $email = (new Email())
                ->from($this->fromEmail)
                ->to($to)
                ->subject($subject)
                ->html($html);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            error_log('[EmailService] custom: ' . $e->getMessage());
        }
    }

    private function isFirstReservation(Reservation $reservation): bool
    {
        $email = $reservation->getEmail();
        if (!$email) {
            return false;
        }

        return count($this->reservationRepository->findBy(['email' => $email])) === 1;
    }
}
