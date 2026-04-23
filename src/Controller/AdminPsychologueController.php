<?php

namespace App\Controller;

use App\Entity\Psychologue;
use App\Form\PsychologueType;
use App\Service\PsychologueMetierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/admin/psychologues')]
class AdminPsychologueController extends AbstractController
{
    #[Route('/', name: 'admin_psychologue_index')]
    public function index(Request $request, PsychologueMetierService $psychologueMetier): Response
    {
        return $this->render('back/psychologue/index.html.twig', $psychologueMetier->buildListeData($request));
    }

    #[Route('/new', name: 'admin_psychologue_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $psychologue = new Psychologue();
        $form = $this->createForm(PsychologueType::class, $psychologue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $psychologue->syncWithUser();
            $entityManager->persist($psychologue);
            $entityManager->flush();
            $this->addFlash('success', 'Psychologue cree avec succes.');

            return $this->redirectToRoute('admin_psychologue_index');
        }

        return $this->render('back/psychologue/form.html.twig', ['form' => $form->createView(), 'psychologue' => $psychologue, 'is_edit' => false]);
    }

    #[Route('/{id}/edit', name: 'admin_psychologue_edit')]
    public function edit(Psychologue $psychologue, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PsychologueType::class, $psychologue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $psychologue->syncWithUser();
            $entityManager->flush();
            $this->addFlash('success', 'Psychologue modifie avec succes.');

            return $this->redirectToRoute('admin_psychologue_index');
        }

        return $this->render('back/psychologue/form.html.twig', ['form' => $form->createView(), 'psychologue' => $psychologue, 'is_edit' => true]);
    }

    #[Route('/{id}', name: 'admin_psychologue_show')]
    public function show(Psychologue $psychologue): Response
    {
        return $this->render('back/psychologue/show.html.twig', ['psychologue' => $psychologue]);
    }

    #[Route('/{id}/delete', name: 'admin_psychologue_delete', methods: ['POST'])]
    public function delete(Psychologue $psychologue, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $psychologue->getIdPsychologue(), (string) $request->request->get('_token'))) {
            $entityManager->remove($psychologue);
            $entityManager->flush();
            $this->addFlash('success', 'Psychologue supprime.');
        }

        return $this->redirectToRoute('admin_psychologue_index');
    }
}
