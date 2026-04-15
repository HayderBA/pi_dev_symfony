<?php

namespace App\Controller;

use App\Entity\SleepTracking;
use App\Form\SleepTrackingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sante-bien-etre/sleep-tracking')]
final class SleepTrackingController extends AbstractController
{
    #[Route(name: 'app_sleep_tracking_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $sleepTrackings = $entityManager
            ->getRepository(SleepTracking::class)
            ->findAll();

        return $this->render('sleep_tracking/index.html.twig', [
            'sleep_trackings' => $sleepTrackings,
        ]);
    }

    #[Route('/new', name: 'app_sleep_tracking_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sleepTracking = new SleepTracking();
        $form = $this->createForm(SleepTrackingType::class, $sleepTracking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sleepTracking);
            $entityManager->flush();
            $this->addFlash('success', 'Sleep tracking entry created successfully.');

            return $this->redirectToRoute('app_sleep_tracking_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Unable to create the sleep tracking entry. Please check the form fields.');
        }

        return $this->render('sleep_tracking/new.html.twig', [
            'sleep_tracking' => $sleepTracking,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sleep_tracking_show', methods: ['GET'])]
    public function show(SleepTracking $sleepTracking): Response
    {
        return $this->render('sleep_tracking/show.html.twig', [
            'sleep_tracking' => $sleepTracking,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sleep_tracking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SleepTracking $sleepTracking, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SleepTrackingType::class, $sleepTracking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Sleep tracking entry updated successfully.');

            return $this->redirectToRoute('app_sleep_tracking_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Unable to update the sleep tracking entry. Please check the form fields.');
        }

        return $this->render('sleep_tracking/edit.html.twig', [
            'sleep_tracking' => $sleepTracking,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sleep_tracking_delete', methods: ['POST'])]
    public function delete(Request $request, SleepTracking $sleepTracking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sleepTracking->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sleepTracking);
            $entityManager->flush();
            $this->addFlash('success', 'Sleep tracking entry deleted successfully.');
        } else {
            $this->addFlash('error', 'Unable to delete the sleep tracking entry. Invalid security token.');
        }

        return $this->redirectToRoute('app_sleep_tracking_index', [], Response::HTTP_SEE_OTHER);
    }
}
