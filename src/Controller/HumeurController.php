<?php

namespace App\Controller;

use App\Entity\Humeur;
use App\Form\HumeurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bien-etre/humeurs')]
final class HumeurController extends AbstractController
{
    #[Route('/', name: 'app_humeur_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('wellness/humeurs/index.html.twig', [
            'humeurs' => $entityManager->getRepository(Humeur::class)->findBy([], ['dateCreation' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_humeur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $humeur = new Humeur();
        $form = $this->createForm(HumeurType::class, $humeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($humeur);
            $entityManager->flush();
            $this->addFlash('success', 'Humeur ajoutee avec succes.');

            return $this->redirectToRoute('app_humeur_index');
        }

        return $this->render('wellness/humeurs/form.html.twig', [
            'title' => 'Nouvelle humeur',
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_humeur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Humeur $humeur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HumeurType::class, $humeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Humeur mise a jour.');

            return $this->redirectToRoute('app_humeur_index');
        }

        return $this->render('wellness/humeurs/form.html.twig', [
            'title' => 'Modifier humeur',
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_humeur_delete', methods: ['POST'])]
    public function delete(Request $request, Humeur $humeur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_humeur_'.$humeur->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($humeur);
            $entityManager->flush();
            $this->addFlash('success', 'Humeur supprimee.');
        }

        return $this->redirectToRoute('app_humeur_index');
    }
}
