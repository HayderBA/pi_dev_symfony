<?php
// src/Controller/EvenementController.php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EvenementController extends AbstractController
{
    #[Route('/evenement', name: 'app_evenement_list', methods: ['GET'])]
    public function list(EvenementRepository $repository, Request $request): Response
    {
        $search = $request->query->get('search');
        $evenements = $search ? $repository->searchEvents($search) : $repository->findBy([], ['date' => 'ASC']);
        return $this->render('front/evenement_index.html.twig', ['evenements' => $evenements]);
    }
    
    #[Route('/evenement/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('front/evenement_show.html.twig', ['evenement' => $evenement]);
    }
    
    #[Route('/admin/evenement', name: 'app_evenement_admin', methods: ['GET'])]
    public function admin(EvenementRepository $repository): Response
    {
        $evenements = $repository->findBy([], ['date' => 'DESC']);
        return $this->render('back/evenement_admin.html.twig', ['evenements' => $evenements]);
    }
    
    #[Route('/admin/evenement/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        
        // Valeurs par défaut
        $evenement->setDate(new \DateTime('+1 day'));
        $evenement->setDynamicPrice(50);
        $evenement->setMaxCapacity(100);
        
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($evenement->getDate() < new \DateTime()) {
                $this->addFlash('error', 'La date ne peut pas être dans le passé');
                return $this->redirectToRoute('app_evenement_new');
            }
            $em->persist($evenement);
            $em->flush();
            $this->addFlash('success', '✓ Événement créé avec succès !');
            return $this->redirectToRoute('app_evenement_admin');
        }
        
        return $this->render('back/evenement_new.html.twig', ['form' => $form->createView()]);
    }
    
    #[Route('/admin/evenement/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✓ Événement modifié avec succès !');
            return $this->redirectToRoute('app_evenement_admin');
        }
        
        return $this->render('back/evenement_edit.html.twig', ['form' => $form->createView(), 'evenement' => $evenement]);
    }
    
    #[Route('/admin/evenement/{id}/delete', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            foreach ($evenement->getReservations() as $reservation) {
                $em->remove($reservation);
            }
            $em->remove($evenement);
            $em->flush();
            $this->addFlash('success', '✓ Événement et ses réservations supprimés avec succès !');
        }
        return $this->redirectToRoute('app_evenement_admin');
    }
    
    #[Route('/admin/evenement/{id}', name: 'app_evenement_show_admin', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showAdmin(int $id, EvenementRepository $repository): Response
    {
        $evenement = $repository->find($id);
        
        if (!$evenement) {
            $this->addFlash('error', 'Événement non trouvé');
            return $this->redirectToRoute('app_evenement_admin');
        }
        
        return $this->render('back/evenement_show.html.twig', ['evenement' => $evenement]);
    }
    
    #[Route('/admin/evenement/stats', name: 'app_evenement_stats', methods: ['GET'])]
    public function stats(EvenementRepository $repository): Response
    {
        $evenements = $repository->findAll();
        
        $totalEvents = count($evenements);
        $totalReservations = 0;
        $totalCapacity = 0;
        
        foreach ($evenements as $event) {
            $totalReservations += $event->getCurrentReservationsCount();
            $totalCapacity += $event->getMaxCapacity();
        }
        
        $occupancyRate = $totalCapacity > 0 ? round(($totalReservations / $totalCapacity) * 100, 1) : 0;
        
        return $this->render('back/stats_evenement.html.twig', [
            'totalEvents' => $totalEvents,
            'totalReservations' => $totalReservations,
            'totalCapacity' => $totalCapacity,
            'occupancyRate' => $occupancyRate,
            'evenements' => $evenements
        ]);
    }
}