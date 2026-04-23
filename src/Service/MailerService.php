<?php

namespace App\Service;

use App\Entity\Ressource;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromEmail,
        private string $fromName,
        private string $notifyEmail
    ) {}

    public function sendNewRessourceNotification(Ressource $ressource): void
    {
        $hasPdf = str_starts_with((string) $ressource->getContent(), 'PDF::');

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to(new Address($this->notifyEmail))
            ->subject('🆕 Nouvelle ressource publiée : ' . $ressource->getTitle())
            ->htmlTemplate('emails/new_ressource.html.twig')
            ->context([
                'ressource'  => $ressource,
                'hasPdf'     => $hasPdf,
                'publishedAt' => new \DateTime(),
            ]);

        $this->mailer->send($email);
    }
}
