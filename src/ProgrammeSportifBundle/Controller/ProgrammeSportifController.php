<?php

namespace App\ProgrammeSportifBundle\Controller;

use App\ProgrammeSportifBundle\Entity\ProgrammeSportif;
use App\ProgrammeSportifBundle\Form\ProgrammeSportifType;
use App\ProgrammeSportifBundle\Repository\ProgrammeSportifRepository;
use App\ProgrammeSportifBundle\Service\ProgrammeSportifGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/programme-sportif', name: 'app_programme_sportif_')]
final class ProgrammeSportifController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ProgrammeSportifRepository $repository): Response
    {
        return $this->render('programme_sportif/index.html.twig', [
            'programmes' => $repository->findLatest(),
        ]);
    }

    #[Route('/generer', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ProgrammeSportifGenerator $generator,
        ProgrammeSportifRepository $repository,
    ): Response {
        $programme = new ProgrammeSportif();
        $form = $this->createForm(ProgrammeSportifType::class, $programme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $generator->generate($programme);
            $entityManager->persist($programme);
            $entityManager->flush();

            $this->addFlash('success', 'Programme sportif genere avec succes.');

            return $this->redirectToRoute('app_programme_sportif_show', [
                'id' => $programme->getId(),
            ]);
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Le programme n a pas pu etre genere. Verifiez les champs du formulaire.');
        }

        return $this->render('programme_sportif/new.html.twig', [
            'form' => $form,
            'recent_programmes' => $repository->findLatest(4),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(ProgrammeSportif $programme, ProgrammeSportifRepository $repository): Response
    {
        $latestForUser = null;

        if (null !== $programme->getUser()) {
            $latestForUser = $repository->findLatestByUser($programme->getUser());
        }

        return $this->render('programme_sportif/show.html.twig', [
            'programme' => $programme,
            'latest_for_user' => $latestForUser,
        ]);
    }
}
