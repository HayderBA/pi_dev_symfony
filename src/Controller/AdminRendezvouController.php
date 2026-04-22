<?php

namespace App\Controller;

use App\Service\NtfyService;
use App\Entity\Rendezvou;
use App\Form\RendezvouType;
use App\Service\RendezVousMetierService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin/rendezvous')]
class AdminRendezvouController extends AbstractController
{
    #[Route('/', name: 'admin_rendezvous_index')]
    public function index(Request $request, RendezVousMetierService $rendezVousMetier, PaginatorInterface $paginator): Response
    {
        $data = $rendezVousMetier->buildListeData($request);
        
        // Pagination : 10 résultats par page
        $rendezvous = $paginator->paginate(
            $data['rendezvous'],  // La requête ou les données à paginer
            $request->query->getInt('page', 1),  // Numéro de page
            10  // Nombre d'éléments par page
        );
        
        // Remplacer les données paginées
        $data['rendezvous'] = $rendezvous;
        
        return $this->render('back/rendezvous_index.html.twig', $data);
    }

    #[Route('/new', name: 'admin_rendezvous_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $rdv = new Rendezvou();
        $rdv->setStatut('en_attente');

        $defaultPsychologueId = $em->getConnection()->fetchOne(
            'SELECT idPsychologue FROM psychologue ORDER BY idPsychologue ASC LIMIT 1'
        );

        if ($defaultPsychologueId === false) {
            $this->addFlash('danger', 'Impossible de créer un rendez-vous : aucun psychologue n’est enregistré en base.');

            return $this->redirectToRoute('admin_rendezvous_index');
        }

        $rdv->setIdPsychologue((int) $defaultPsychologueId);

        $form = $this->createForm(RendezvouType::class, $rdv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($rdv);
            $em->flush();
            $this->addFlash('success', 'Rendez-vous cree');

            return $this->redirectToRoute('admin_rendezvous_index');
        }

        return $this->render('back/rendezvous_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_rendezvous_show')]
    public function show(Rendezvou $rdv): Response
    {
        return $this->render('back/rendezvous_show.html.twig', [
            'rdv' => $rdv,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_rendezvous_edit')]
    public function edit(Rendezvou $rdv, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RendezvouType::class, $rdv, ['include_statut' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Rendez-vous modifié');
            return $this->redirectToRoute('admin_rendezvous_index');
        }

        return $this->render('back/rendezvous_edit.html.twig', [
            'form' => $form->createView(),
            'rdv' => $rdv
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_rendezvous_delete', methods: ['POST'])]
    public function delete(Rendezvou $rdv, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rdv->getIdRdv(), $request->request->get('_token'))) {
            $em->remove($rdv);
            $em->flush();
            $this->addFlash('success', 'Rendez-vous supprimé');
        }
        return $this->redirectToRoute('admin_rendezvous_index');
    }

    #[Route('/{id}/confirm', name: 'admin_rendezvous_confirm')]
    public function confirm(Rendezvou $rdv, EntityManagerInterface $em, NtfyService $ntfy): Response
    {
        $rdv->setStatut('confirme');
        $em->flush();
        
        // ========== ENVOI NOTIFICATION NTFY ==========
        $topic = 'cabinet_medical';
        $title = '✅ Rendez-vous confirmé';
        $message = "Bonjour " . $rdv->getPrenomPatient() . ",\nVotre rendez-vous du " . $rdv->getDateRdv()->format('d/m/Y') . " à " . $rdv->getHeure()->format('H:i') . " a été CONFIRMÉ.";
        
        $ntfy->send($topic, $title, $message);
        // ============================================
        
        $this->addFlash('success', 'Rendez-vous confirmé');
        return $this->redirectToRoute('admin_rendezvous_index');
    }

    #[Route('/{id}/cancel', name: 'admin_rendezvous_cancel')]
    public function cancel(Rendezvou $rdv, EntityManagerInterface $em, NtfyService $ntfy): Response
    {
        $rdv->setStatut('annule');
        $em->flush();
        
        // ========== ENVOI NOTIFICATION NTFY ==========
        $topic = 'cabinet_medical';
        $title = '❌ Rendez-vous annulé';
        $message = "Bonjour " . $rdv->getPrenomPatient() . ",\nVotre rendez-vous du " . $rdv->getDateRdv()->format('d/m/Y') . " à " . $rdv->getHeure()->format('H:i') . " a été ANNULÉ.";
        
        $ntfy->send($topic, $title, $message);
        // ============================================
        
        $this->addFlash('success', 'Rendez-vous annulé');
        return $this->redirectToRoute('admin_rendezvous_index');
    }
}