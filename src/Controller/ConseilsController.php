<?php

namespace App\Controller;

use App\Entity\Conseils;
use App\Form\ConseilsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sante-bien-etre/conseils')]
final class ConseilsController extends AbstractController
{
    #[Route(name: 'app_conseils_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $conseils = $entityManager
            ->getRepository(Conseils::class)
            ->findAll();

        return $this->render('conseils/index.html.twig', [
            'conseils' => $conseils,
        ]);
    }

    #[Route('/new', name: 'app_conseils_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $conseil = new Conseils();
        $form = $this->createForm(ConseilsType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($conseil);
            $entityManager->flush();
            $this->addFlash('success', 'Conseil created successfully.');

            return $this->redirectToRoute('app_conseils_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Unable to create the conseil. Please check the form fields.');
        }

        return $this->render('conseils/new.html.twig', [
            'conseil' => $conseil,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conseils_show', methods: ['GET'])]
    public function show(Conseils $conseil): Response
    {
        return $this->render('conseils/show.html.twig', [
            'conseil' => $conseil,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conseils_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conseils $conseil, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConseilsType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Conseil updated successfully.');

            return $this->redirectToRoute('app_conseils_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Unable to update the conseil. Please check the form fields.');
        }

        return $this->render('conseils/edit.html.twig', [
            'conseil' => $conseil,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conseils_delete', methods: ['POST'])]
    public function delete(Request $request, Conseils $conseil, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conseil->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($conseil);
            $entityManager->flush();
            $this->addFlash('success', 'Conseil deleted successfully.');
        } else {
            $this->addFlash('error', 'Unable to delete the conseil. Invalid security token.');
        }

        return $this->redirectToRoute('app_conseils_index', [], Response::HTTP_SEE_OTHER);
    }
}
