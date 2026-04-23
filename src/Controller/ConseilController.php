<?php

namespace App\Controller;

use App\Entity\Conseil;
use App\Form\ConseilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bien-etre/conseils')]
final class ConseilController extends AbstractController
{
    #[Route('/', name: 'app_conseil_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('wellness/conseils/index.html.twig', [
            'conseils' => $entityManager->getRepository(Conseil::class)->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_conseil_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $conseil = new Conseil();
        $form = $this->createForm(ConseilType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($conseil);
            $entityManager->flush();
            $this->addFlash('success', 'Conseil ajoute avec succes.');

            return $this->redirectToRoute('app_conseil_index');
        }

        return $this->render('wellness/conseils/form.html.twig', [
            'title' => 'Nouveau conseil',
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conseil_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conseil $conseil, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConseilType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Conseil mis a jour.');

            return $this->redirectToRoute('app_conseil_index');
        }

        return $this->render('wellness/conseils/form.html.twig', [
            'title' => 'Modifier conseil',
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conseil_delete', methods: ['POST'])]
    public function delete(Request $request, Conseil $conseil, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_conseil_'.$conseil->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($conseil);
            $entityManager->flush();
            $this->addFlash('success', 'Conseil supprime.');
        }

        return $this->redirectToRoute('app_conseil_index');
    }
}
