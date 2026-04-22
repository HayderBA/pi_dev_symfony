<?php

namespace App\Controller;

use App\Entity\Rendezvou;  
use App\Repository\RendezvouRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    #[Route('/api/rdv/rappel', name: 'api_rdv_rappel', methods: ['GET'])]
    public function getRdvsForReminder(RendezvouRepository $repo): Response
    {
        $tomorrow = (new \DateTime('+1 day'))->setTime(0, 0, 0);
        
        $rdvs = $repo->createQueryBuilder('r')
            ->where('r.dateRdv = :date')
            ->andWhere('r.rappel_envoye = false OR r.rappel_envoye IS NULL')
            ->andWhere('r.statut != :annule')
            ->setParameter('date', $tomorrow)
            ->setParameter('annule', 'annule')
            ->getQuery()
            ->getResult();
        
        $data = [];
        foreach ($rdvs as $rdv) {
            $data[] = [
                'idRdv' => $rdv->getIdRdv(),
                'patient_nom' => $rdv->getNomPatient(),
                'patient_prenom' => $rdv->getPrenomPatient(),
                'patient_email' => $rdv->getEmailPatient(),
                'date' => $rdv->getDateRdv()->format('d/m/Y'),
                'heure' => $rdv->getHeure()->format('H:i'),
                'type' => $rdv->getTypeCons(),
            ];
        }
        
        return $this->json($data);
    }

    #[Route('/api/rdv/rappel/sent/{idRdv}', name: 'api_rdv_rappel_sent', methods: ['POST'])]
    public function markReminderSent(Rendezvou $rdv, EntityManagerInterface $em): Response
    {
        $rdv->setRappelEnvoye(true);
        $rdv->setDateRappel(new \DateTime());
        $em->flush();
        
        return $this->json(['success' => true]);
    }
}