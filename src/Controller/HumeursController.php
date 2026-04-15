<?php

namespace App\Controller;

use App\Entity\Humeurs;
use App\Form\HumeursType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sante-bien-etre/humeurs')]
final class HumeursController extends AbstractController
{
    #[Route(name: 'app_humeurs_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $humeurs = $entityManager
            ->getRepository(Humeurs::class)
            ->findAll();

        return $this->render('humeurs/index.html.twig', [
            'humeurs' => $humeurs,
        ]);
    }

    #[Route('/new', name: 'app_humeurs_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $humeur = new Humeurs();
        $form = $this->createForm(HumeursType::class, $humeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($humeur);
            $entityManager->flush();
            $this->addFlash('success', 'Humeur entry created successfully.');

            return $this->redirectToRoute('app_humeurs_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Unable to create the humeur entry. Please check the form fields.');
        }

        return $this->render('humeurs/new.html.twig', [
            'humeur' => $humeur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_humeurs_show', methods: ['GET'])]
    public function show(Humeurs $humeur): Response
    {
        return $this->render('humeurs/show.html.twig', [
            'humeur' => $humeur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_humeurs_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Humeurs $humeur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HumeursType::class, $humeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Humeur entry updated successfully.');

            return $this->redirectToRoute('app_humeurs_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Unable to update the humeur entry. Please check the form fields.');
        }

        return $this->render('humeurs/edit.html.twig', [
            'humeur' => $humeur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_humeurs_delete', methods: ['POST'])]
    public function delete(Request $request, Humeurs $humeur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$humeur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($humeur);
            $entityManager->flush();
            $this->addFlash('success', 'Humeur entry deleted successfully.');
        } else {
            $this->addFlash('error', 'Unable to delete the humeur entry. Invalid security token.');
        }

        return $this->redirectToRoute('app_humeurs_index', [], Response::HTTP_SEE_OTHER);
    }
}
