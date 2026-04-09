<?php

namespace App\Controller;

use App\Entity\Rendezvou;
use App\Form\RendezvouType;
use App\Service\RendezVousMetierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontRendezvouController extends AbstractController
{
    #[Route('/rendezvous', name: 'front_rendezvous_index')]
    public function index(Request $request, RendezVousMetierService $rendezVousMetier): Response
    {
        return $this->render('front/rendezvous_index.html.twig', $rendezVousMetier->buildListeData($request));
    }

    #[Route('/prendre-rendez-vous', name: 'front_rendezvous_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $rdv = new Rendezvou();
        $rdv->setStatut('en_attente');
        $conn = $em->getConnection();
        $cabinetsRows = $conn->fetchAllAssociative(
            'SELECT idCabinet, nomcabinet FROM cabinet ORDER BY nomcabinet ASC'
        );
        $cabinetChoices = [];
        foreach ($cabinetsRows as $row) {
            $cabinetChoices[(string) $row['nomcabinet']] = (int) $row['idCabinet'];
        }

        if ($cabinetChoices === []) {
            $this->addFlash('danger', 'Aucun cabinet disponible pour prendre un rendez-vous.');
            return $this->redirectToRoute('front_cabinet_index');
        }

        $posted = $request->request->all('rendezvou');
        $selectedCabinetId = isset($posted['cabinet_id']) && $posted['cabinet_id'] !== ''
            ? (int) $posted['cabinet_id']
            : null;

        $psyRows = $conn->fetchAllAssociative(
            'SELECT idPsychologue, idCabinet FROM psychologue ORDER BY idPsychologue ASC'
        );
        $psychologueChoices = [];
        $psychologuesByCabinet = [];
        foreach ($psyRows as $row) {
            $id = (int) $row['idPsychologue'];
            $cab = (int) $row['idCabinet'];
            $psychologueChoices['Psychologue #' . $id] = $id;
            if (!isset($psychologuesByCabinet[$cab])) {
                $psychologuesByCabinet[$cab] = [];
            }
            $psychologuesByCabinet[$cab][] = $id;
        }

        $form = $this->createForm(RendezvouType::class, $rdv, [
            'include_cabinet_choice' => true,
            'include_psychologue_choice' => true,
            'cabinet_choices' => $cabinetChoices,
            'psychologue_choices' => $psychologueChoices,
            'selected_cabinet_id' => $selectedCabinetId,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedCabinetId = (int) $form->get('cabinet_id')->getData();
            $selectedPsychologueId = $rdv->getIdPsychologue();

            $belongs = (int) $conn->fetchOne(
                'SELECT COUNT(*) FROM psychologue WHERE idPsychologue = :psy AND idCabinet = :cab',
                ['psy' => $selectedPsychologueId, 'cab' => $selectedCabinetId]
            );

            if ($belongs === 0) {
                $form->get('idPsychologue')->addError(
                    new FormError('Le psychologue choisi ne correspond pas au cabinet sélectionné.')
                );
            } else {
            $em->persist($rdv);
            $em->flush();

            $this->addFlash('success', 'Votre rendez-vous a été pris avec succès !');
            return $this->redirectToRoute('front_rendezvous_index');
            }
        }

        return $this->render('front/rendezvous_new.html.twig', [
            'form' => $form->createView(),
            'psychologues_by_cabinet' => $psychologuesByCabinet,
        ]);
    }

    #[Route('/rendezvous/{id}', name: 'front_rendezvous_show')]
    public function show(Rendezvou $rdv): Response
    {
        return $this->render('front/rendezvous_show.html.twig', [
            'rdv' => $rdv,
        ]);
    }

    #[Route('/rendezvous/{id}/edit', name: 'front_rendezvous_edit')]
    public function edit(Rendezvou $rdv, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RendezvouType::class, $rdv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Rendez-vous modifie avec succes.');

            return $this->redirectToRoute('front_rendezvous_index');
        }

        return $this->render('front/rendezvous_edit.html.twig', [
            'form' => $form->createView(),
            'rdv' => $rdv,
        ]);
    }

    #[Route('/rendezvous/{id}/delete', name: 'front_rendezvous_delete', methods: ['POST'])]
    public function delete(Rendezvou $rdv, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_front_rdv' . $rdv->getIdRdv(), $request->request->get('_token'))) {
            $em->remove($rdv);
            $em->flush();
            $this->addFlash('success', 'Rendez-vous supprime avec succes.');
        }

        return $this->redirectToRoute('front_rendezvous_index');
    }
}