<?php

namespace App\Controller;

use App\Entity\Psychologue;
use App\Entity\Rendezvous;
use App\Form\RendezvousType;
use App\Repository\CabinetRepository;
use App\Repository\PsychologueRepository;
use App\Repository\RendezvousRepository;
use App\Service\CabinetMailerService;
use App\Service\RendezVousMetierService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontRendezvousController extends AbstractController
{
    #[Route('/rendezvous', name: 'front_rendezvous_index')]
    public function index(Request $request, RendezVousMetierService $metierService, PaginatorInterface $paginator, RendezvousRepository $rendezvousRepository): Response
    {
        $data = $metierService->buildListeData($request);
        $data['rendezvous'] = $paginator->paginate($data['rendezvous'], $request->query->getInt('page', 1), 10);
        return $this->render('front/rendezvous_index.html.twig', $data);
    }

    #[Route('/prendre-rendez-vous', name: 'front_rendezvous_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, CabinetRepository $cabinetRepository, PsychologueRepository $psychologueRepository, CabinetMailerService $mailerService): Response
    {
        $rdv = (new Rendezvous())->setStatut('en_attente');
        $form = $this->createRendezvousForm($rdv, $request, $cabinetRepository, $psychologueRepository);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedPsychologueId = (int) $form->get('psychologue_id')->getData();
            $psychologue = $psychologueRepository->find($selectedPsychologueId);

            if (!$psychologue instanceof Psychologue) {
                $form->get('psychologue_id')->addError(new FormError('Psychologue invalide.'));
            } else {
                $rdv->setPsychologue($psychologue);
                $rdv->setTarifConsultation($psychologue->getTarif());
                $entityManager->persist($rdv);
                $entityManager->flush();
                $mailerService->sendBookingCreated($rdv);
                $this->addFlash('success', 'Votre rendez-vous a ete pris avec succes.');

                return $this->redirectToRoute('front_rendezvous_show', ['id' => $rdv->getIdRdv()]);
            }
        }

        return $this->render('front/rendezvous_new.html.twig', [
            'form' => $form->createView(),
            'rdv' => $rdv,
            'psychologues_by_cabinet' => $this->buildPsychologuesByCabinet($psychologueRepository),
        ]);
    }

    #[Route('/rendezvous/{id}', name: 'front_rendezvous_show')]
    public function show(Rendezvous $rdv): Response
    {
        return $this->render('front/rendezvous_show.html.twig', ['rdv' => $rdv]);
    }

    #[Route('/rendezvous/{id}/edit', name: 'front_rendezvous_edit')]
    public function edit(Rendezvous $rdv, Request $request, EntityManagerInterface $entityManager, CabinetRepository $cabinetRepository, PsychologueRepository $psychologueRepository): Response
    {
        $form = $this->createRendezvousForm($rdv, $request, $cabinetRepository, $psychologueRepository);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedPsychologueId = (int) $form->get('psychologue_id')->getData();
            $psychologue = $psychologueRepository->find($selectedPsychologueId);

            if (!$psychologue instanceof Psychologue) {
                $form->get('psychologue_id')->addError(new FormError('Psychologue invalide.'));
            } else {
                $rdv->setPsychologue($psychologue);
                $rdv->setTarifConsultation($psychologue->getTarif());
                $entityManager->flush();
                $this->addFlash('success', 'Le rendez-vous a été modifié avec succès.');

                return $this->redirectToRoute('front_rendezvous_index');
            }
        }

        return $this->render('front/rendezvous_edit.html.twig', [
            'form' => $form->createView(),
            'rdv' => $rdv,
            'psychologues_by_cabinet' => $this->buildPsychologuesByCabinet($psychologueRepository),
        ]);
    }

    #[Route('/rendezvous/{id}/delete', name: 'front_rendezvous_delete', methods: ['POST'])]
    public function delete(Rendezvous $rdv, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_front_rdv' . $rdv->getIdRdv(), (string) $request->request->get('_token'))) {
            $entityManager->remove($rdv);
            $entityManager->flush();
            $this->addFlash('success', 'Le rendez-vous a été supprimé.');
        }

        return $this->redirectToRoute('front_rendezvous_index');
    }

    private function createRendezvousForm(Rendezvous $rdv, Request $request, CabinetRepository $cabinetRepository, PsychologueRepository $psychologueRepository)
    {
        $posted = $request->request->all('rendezvous');
        $selectedCabinetId = isset($posted['cabinet_id']) && '' !== $posted['cabinet_id']
            ? (int) $posted['cabinet_id']
            : $rdv->getPsychologue()?->getCabinet()?->getIdCabinet();
        $selectedPsychologueId = isset($posted['psychologue_id']) && '' !== $posted['psychologue_id']
            ? (int) $posted['psychologue_id']
            : $rdv->getPsychologue()?->getIdPsychologue();

        $cabinetChoices = [];
        foreach ($cabinetRepository->findBy(['status' => 'actif'], ['nomcabinet' => 'ASC']) as $cabinet) {
            $cabinetChoices[$cabinet->getNomcabinet()] = $cabinet->getIdCabinet();
        }

        $psychologueChoices = [];
        $psychologues = $selectedCabinetId
            ? $psychologueRepository->findByCabinetId($selectedCabinetId)
            : $psychologueRepository->findFiltered(null, null);
        foreach ($psychologues as $psychologue) {
            $psychologueChoices[$psychologue->getDisplayName()] = $psychologue->getIdPsychologue();
        }

        return $this->createForm(RendezvousType::class, $rdv, [
            'include_cabinet_choice' => true,
            'include_psychologue_choice' => true,
            'cabinet_choices' => $cabinetChoices,
            'psychologue_choices' => $psychologueChoices,
            'selected_cabinet_id' => $selectedCabinetId,
            'selected_psychologue_id' => $selectedPsychologueId,
        ]);
    }

    private function buildPsychologuesByCabinet(PsychologueRepository $psychologueRepository): array
    {
        $grouped = [];

        foreach ($psychologueRepository->findFiltered(null, null) as $psychologue) {
            $cabinetId = $psychologue->getCabinet()?->getIdCabinet();
            if (null === $cabinetId) {
                continue;
            }

            $grouped[$cabinetId][] = $psychologue->getIdPsychologue();
        }

        return $grouped;
    }
}
