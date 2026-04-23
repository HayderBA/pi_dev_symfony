<?php

namespace App\Command;

use App\Repository\ReservationRepository;
use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-reminders',
    description: 'Envoie les emails de rappel pour demain et de satisfaction pour hier.'
)]
class SendRemindersCommand extends Command
{
    public function __construct(
        private readonly ReservationRepository $reservationRepository,
        private readonly EmailService $emailService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Envoi automatique des emails GrowMind');

        // Les evenements sont stockes avec une date sans heure.
        // On travaille donc par jour complet pour eviter de rater les rappels.
        $tomorrow = (new \DateTimeImmutable('tomorrow'))->format('Y-m-d');
        $yesterday = (new \DateTimeImmutable('yesterday'))->format('Y-m-d');

        $reminderReservations = $this->reservationRepository->createQueryBuilder('r')
            ->join('r.evenement', 'e')
            ->where('e.date = :tomorrow')
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getResult();

        $satisfactionReservations = $this->reservationRepository->createQueryBuilder('r')
            ->join('r.evenement', 'e')
            ->where('e.date = :yesterday')
            ->setParameter('yesterday', $yesterday)
            ->getQuery()
            ->getResult();

        $reminders = 0;
        foreach ($reminderReservations as $reservation) {
            $this->emailService->sendReminderEmail($reservation);
            ++$reminders;
        }

        $satisfactions = 0;
        $offers = 0;
        foreach ($satisfactionReservations as $reservation) {
            $this->emailService->sendSatisfactionEmail($reservation);
            $this->emailService->sendSpecialOfferEmail($reservation);
            ++$satisfactions;
            ++$offers;
        }

        $io->success(sprintf(
            'Emails envoyes: %d rappels, %d satisfactions, %d offres speciales.',
            $reminders,
            $satisfactions,
            $offers
        ));

        $io->writeln('Cron conseille: `0 8 * * * php bin/console app:send-reminders`');

        return Command::SUCCESS;
    }
}
