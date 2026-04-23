<?php

namespace App\Controller;

use App\Entity\SleepTracking;
use App\Form\SleepTrackingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bien-etre/sommeil')]
final class SleepTrackingController extends AbstractController
{
    #[Route('/', name: 'app_sleep_tracking_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('wellness/sleep/index.html.twig', [
            'sleep_trackings' => $entityManager->getRepository(SleepTracking::class)->findBy([], ['dateSommeil' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_sleep_tracking_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entry = new SleepTracking();
        $form = $this->createForm(SleepTrackingType::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entry);
            $entityManager->flush();
            $this->addFlash('success', 'Suivi sommeil ajoute avec succes.');

            return $this->redirectToRoute('app_sleep_tracking_index');
        }

        return $this->render('wellness/sleep/form.html.twig', [
            'title' => 'Nouveau suivi sommeil',
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sleep_tracking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SleepTracking $entry, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SleepTrackingType::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Suivi sommeil mis a jour.');

            return $this->redirectToRoute('app_sleep_tracking_index');
        }

        return $this->render('wellness/sleep/form.html.twig', [
            'title' => 'Modifier suivi sommeil',
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sleep_tracking_delete', methods: ['POST'])]
    public function delete(Request $request, SleepTracking $entry, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_sleep_'.$entry->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($entry);
            $entityManager->flush();
            $this->addFlash('success', 'Suivi sommeil supprime.');
        }

        return $this->redirectToRoute('app_sleep_tracking_index');
    }
}
