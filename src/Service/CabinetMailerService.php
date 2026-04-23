<?php

namespace App\Service;

use App\Entity\Rendezvous;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class CabinetMailerService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        #[Autowire('%mailer.from_email%')]
        private readonly string $fromEmail,
        #[Autowire('%mailer.from_name%')]
        private readonly string $fromName,
        #[Autowire('%mailer.notify_email%')]
        private readonly string $notifyEmail
    ) {
    }

    public function sendBookingCreated(Rendezvous $rdv): bool
    {
        $sent = false;

        if ($rdv->getEmailPatient()) {
            $sent = $this->send(
                (string) $rdv->getEmailPatient(),
                'Demande de rendez-vous GrowMind bien recue',
                'emails/cabinet/booking_created.html.twig',
                ['rdv' => $rdv]
            ) || $sent;
        }

        return $this->send(
            $this->notifyEmail,
            'Nouvelle demande de rendez-vous cabinet',
            'emails/cabinet/booking_admin_notification.html.twig',
            ['rdv' => $rdv]
        ) || $sent;
    }

    public function sendStatusUpdated(Rendezvous $rdv): bool
    {
        if (!$rdv->getEmailPatient()) {
            return false;
        }

        return $this->send(
            (string) $rdv->getEmailPatient(),
            sprintf('Mise a jour de votre rendez-vous GrowMind: %s', ucfirst((string) $rdv->getStatut())),
            'emails/cabinet/status_updated.html.twig',
            ['rdv' => $rdv]
        );
    }

    public function sendReminder(Rendezvous $rdv): bool
    {
        if (!$rdv->getEmailPatient()) {
            return false;
        }

        return $this->send(
            (string) $rdv->getEmailPatient(),
            'Rappel GrowMind pour votre rendez-vous de demain',
            'emails/cabinet/reminder.html.twig',
            ['rdv' => $rdv]
        );
    }

    public function sendPaymentConfirmation(Rendezvous $rdv): bool
    {
        if (!$rdv->getEmailPatient()) {
            return false;
        }

        return $this->send(
            (string) $rdv->getEmailPatient(),
            'Confirmation de paiement - Consultation GrowMind',
            'emails/cabinet/payment_confirmation.html.twig',
            ['rdv' => $rdv]
        );
    }

    private function send(string $to, string $subject, string $template, array $context): bool
    {
        try {
            $email = (new Email())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($to)
                ->subject($subject)
                ->html($this->twig->render($template, $context));

            $this->mailer->send($email);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
