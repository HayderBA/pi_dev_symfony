<?php

namespace App\Controller;

use App\Entity\Rendezvous;
use App\Repository\RendezvousRepository;
use App\Service\CabinetMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RendezvousWebhookController extends AbstractController
{
    #[Route('/api/rdv/rappel', name: 'api_rdv_rappel', methods: ['GET'])]
    public function getRendezvousForReminder(RendezvousRepository $repository): Response
    {
        $tomorrow = (new \DateTimeImmutable('+1 day'))->setTime(0, 0, 0);
        $dayAfter = $tomorrow->modify('+1 day');

        $rdvs = $repository->createQueryBuilder('r')
            ->leftJoin('r.psychologue', 'p')->addSelect('p')
            ->where('r.dateRdv >= :tomorrow')
            ->andWhere('r.dateRdv < :dayAfter')
            ->andWhere('r.rappel_envoye = false OR r.rappel_envoye IS NULL')
            ->andWhere('r.statut != :annule')
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('dayAfter', $dayAfter)
            ->setParameter('annule', 'annule')
            ->getQuery()
            ->getResult();

        $data = array_map(static fn (Rendezvous $rdv) => [
            'idRdv' => $rdv->getIdRdv(),
            'patient_nom' => $rdv->getNomPatient(),
            'patient_prenom' => $rdv->getPrenomPatient(),
            'patient_email' => $rdv->getEmailPatient(),
            'date' => $rdv->getDateRdv()?->format('d/m/Y'),
            'heure' => $rdv->getHeure()?->format('H:i'),
            'type' => $rdv->getTypeCons(),
            'psychologue' => $rdv->getPsychologue()?->getDisplayName(),
        ], $rdvs);

        return $this->json($data);
    }

    #[Route('/api/rdv/rappel/sent/{idRdv}', name: 'api_rdv_rappel_sent', methods: ['POST'])]
    public function markReminderSent(Rendezvous $rdv, EntityManagerInterface $entityManager): Response
    {
        $rdv->setRappelEnvoye(true);
        $rdv->setDateRappel(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/rdv/rappel/send/{idRdv}', name: 'api_rdv_rappel_send', methods: ['POST'])]
    public function sendReminder(Rendezvous $rdv, Request $request, EntityManagerInterface $entityManager, CabinetMailerService $mailerService): Response
    {
        $token = (string) $request->headers->get('X-Workflow-Token', '');
        $expected = (string) ($_ENV['RDV_WORKFLOW_TOKEN'] ?? '');

        if ('' !== $expected && $token !== $expected) {
            return $this->json(['success' => false, 'message' => 'Token workflow invalide.'], 403);
        }

        $sent = $mailerService->sendReminder($rdv);

        if ($sent) {
            $rdv->setRappelEnvoye(true);
            $rdv->setDateRappel(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->json(['success' => $sent]);
    }
}
