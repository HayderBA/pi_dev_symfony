<?php

namespace App\Controller;

use App\Entity\Psychologue;
use App\Entity\Rendezvous;
use App\Form\RendezvousType;
use App\Repository\CabinetRepository;
use App\Repository\PsychologueRepository;
use App\Repository\RendezvousRepository;
use App\Service\CabinetMailerService;
use App\Service\NtfyService;
use App\Service\RendezVousMetierService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/admin/rendezvous')]
class AdminRendezvousController extends AbstractController
{
    #[Route('/', name: 'admin_rendezvous_index')]
    public function index(Request $request, RendezVousMetierService $metierService, PaginatorInterface $paginator, RendezvousRepository $rendezvousRepository): Response
    {
        $data = $metierService->buildListeData($request);
        $data['rendezvous'] = $paginator->paginate($data['rendezvous'], $request->query->getInt('page', 1), 10);
        return $this->render('back/rendezvous_index.html.twig', $data);
    }

    #[Route('/new', name: 'admin_rendezvous_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, CabinetRepository $cabinetRepository, PsychologueRepository $psychologueRepository): Response
    {
        $rdv = (new Rendezvous())->setStatut('en_attente');
        $form = $this->createRendezvousForm($rdv, $request, $cabinetRepository, $psychologueRepository, false);
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
                $this->addFlash('success', 'Rendez-vous cree.');

                return $this->redirectToRoute('admin_rendezvous_index');
            }
        }

        return $this->render('back/rendezvous_new.html.twig', ['form' => $form->createView(), 'rdv' => $rdv, 'is_edit' => false]);
    }

    #[Route('/{id}', name: 'admin_rendezvous_show')]
    public function show(Rendezvous $rdv): Response
    {
        return $this->render('back/rendezvous_show.html.twig', ['rdv' => $rdv]);
    }

    #[Route('/{id}/edit', name: 'admin_rendezvous_edit')]
    public function edit(Rendezvous $rdv, Request $request, EntityManagerInterface $entityManager, CabinetRepository $cabinetRepository, PsychologueRepository $psychologueRepository): Response
    {
        $form = $this->createRendezvousForm($rdv, $request, $cabinetRepository, $psychologueRepository, true);
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
                $this->addFlash('success', 'Rendez-vous modifie.');

                return $this->redirectToRoute('admin_rendezvous_index');
            }
        }

        return $this->render('back/rendezvous_edit.html.twig', ['form' => $form->createView(), 'rdv' => $rdv, 'is_edit' => true]);
    }

    #[Route('/{id}/delete', name: 'admin_rendezvous_delete', methods: ['POST'])]
    public function delete(Rendezvous $rdv, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rdv->getIdRdv(), (string) $request->request->get('_token'))) {
            $entityManager->remove($rdv);
            $entityManager->flush();
            $this->addFlash('success', 'Rendez-vous supprime.');
        }

        return $this->redirectToRoute('admin_rendezvous_index');
    }

    #[Route('/{id}/confirm', name: 'admin_rendezvous_confirm')]
    public function confirm(Rendezvous $rdv, EntityManagerInterface $entityManager, NtfyService $ntfyService): Response
    {
        $rdv->setStatut('confirme');
        $entityManager->flush();
        $ntfyService->send(
            'cabinet_medical',
            'Rendez-vous confirme',
            sprintf(
                "Bonjour %s,\nVotre rendez-vous du %s a %s a ete CONFIRME.",
                $rdv->getPrenomPatient() ?: 'patient',
                $rdv->getDateRdv()?->format('d/m/Y'),
                $rdv->getHeure()?->format('H:i')
            )
        );
        $this->addFlash('success', 'Rendez-vous confirme.');

        return $this->redirectToRoute('admin_rendezvous_index');
    }

    #[Route('/{id}/cancel', name: 'admin_rendezvous_cancel')]
    public function cancel(Rendezvous $rdv, EntityManagerInterface $entityManager, NtfyService $ntfyService): Response
    {
        $rdv->setStatut('annule');
        $entityManager->flush();
        $ntfyService->send(
            'cabinet_medical',
            'Rendez-vous annule',
            sprintf(
                "Bonjour %s,\nVotre rendez-vous du %s a %s a ete ANNULE.",
                $rdv->getPrenomPatient() ?: 'patient',
                $rdv->getDateRdv()?->format('d/m/Y'),
                $rdv->getHeure()?->format('H:i')
            )
        );
        $this->addFlash('success', 'Rendez-vous annule.');

        return $this->redirectToRoute('admin_rendezvous_index');
    }

    #[Route('/{id}/reminder', name: 'admin_rendezvous_reminder')]
    public function sendReminder(Rendezvous $rdv, EntityManagerInterface $entityManager, CabinetMailerService $mailerService, NtfyService $ntfyService): Response
    {
        $sent = $mailerService->sendReminder($rdv);
        $rdv->setRappelEnvoye($sent);
        $rdv->setDateRappel($sent ? new \DateTimeImmutable() : null);
        $entityManager->flush();

        if ($sent) {
            $ntfyService->send('cabinet_medical', 'Rappel rendez-vous envoye', sprintf('Rappel envoye pour le rendez-vous #%d.', $rdv->getIdRdv()));
            $this->addFlash('success', 'Rappel envoye par email.');
        } else {
            $this->addFlash('error', 'Rappel non envoye. Verifiez l email patient et la configuration mail.');
        }

        return $this->redirectToRoute('admin_rendezvous_show', ['id' => $rdv->getIdRdv()]);
    }

    private function createRendezvousForm(Rendezvous $rdv, Request $request, CabinetRepository $cabinetRepository, PsychologueRepository $psychologueRepository, bool $includeStatut)
    {
        $posted = $request->request->all('rendezvous');
        $selectedCabinetId = isset($posted['cabinet_id']) && '' !== $posted['cabinet_id']
            ? (int) $posted['cabinet_id']
            : $rdv->getPsychologue()?->getCabinet()?->getIdCabinet();
        $selectedPsychologueId = isset($posted['psychologue_id']) && '' !== $posted['psychologue_id']
            ? (int) $posted['psychologue_id']
            : $rdv->getPsychologue()?->getIdPsychologue();

        $cabinetChoices = [];
        foreach ($cabinetRepository->findBy([], ['nomcabinet' => 'ASC']) as $cabinet) {
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
            'include_statut' => $includeStatut,
            'include_cabinet_choice' => true,
            'include_psychologue_choice' => true,
            'cabinet_choices' => $cabinetChoices,
            'psychologue_choices' => $psychologueChoices,
            'selected_cabinet_id' => $selectedCabinetId,
            'selected_psychologue_id' => $selectedPsychologueId,
        ]);
    }
}
