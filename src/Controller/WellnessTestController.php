<?php

namespace App\Controller;

use App\Entity\WellnessTest;
use App\Form\WellnessTestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bien-etre/tests')]
final class WellnessTestController extends AbstractController
{
    #[Route('/', name: 'app_wellness_test_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('wellness/tests/index.html.twig', [
            'tests' => $entityManager->getRepository(WellnessTest::class)->findBy([], ['dateTest' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_wellness_test_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $test = new WellnessTest();
        $form = $this->createForm(WellnessTestType::class, $test);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($test);
            $entityManager->flush();
            $this->addFlash('success', 'Test bien-etre ajoute avec succes.');

            return $this->redirectToRoute('app_wellness_test_index');
        }

        return $this->render('wellness/tests/form.html.twig', [
            'title' => 'Nouveau test',
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_wellness_test_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WellnessTest $test, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WellnessTestType::class, $test);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Test bien-etre mis a jour.');

            return $this->redirectToRoute('app_wellness_test_index');
        }

        return $this->render('wellness/tests/form.html.twig', [
            'title' => 'Modifier test',
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wellness_test_delete', methods: ['POST'])]
    public function delete(Request $request, WellnessTest $test, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_test_'.$test->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($test);
            $entityManager->flush();
            $this->addFlash('success', 'Test bien-etre supprime.');
        }

        return $this->redirectToRoute('app_wellness_test_index');
    }
}
