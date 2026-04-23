<?php

namespace App\Controller;

use App\Entity\ProgrammeSportif;
use App\Form\ProgrammeSportifType;
use App\Service\ProgrammeSportifGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bien-etre/programmes')]
final class ProgrammeSportifController extends AbstractController
{
    #[Route('/', name: 'app_programme_sportif_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('wellness/programmes/index.html.twig', [
            'programmes' => $entityManager->getRepository(ProgrammeSportif::class)->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_programme_sportif_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProgrammeSportifGenerator $generator): Response
    {
        $programme = new ProgrammeSportif();
        $form = $this->createForm(ProgrammeSportifType::class, $programme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $generator->generate($programme);
            $entityManager->persist($programme);
            $entityManager->flush();
            $this->addFlash('success', 'Programme sportif genere avec succes.');

            return $this->redirectToRoute('app_programme_sportif_show', ['id' => $programme->getId()]);
        }

        return $this->render('wellness/programmes/form.html.twig', [
            'title' => 'Generer un programme sportif',
            'form' => $form,
            'recent_programmes' => $entityManager->getRepository(ProgrammeSportif::class)->findBy([], ['createdAt' => 'DESC'], 3),
        ]);
    }

    #[Route('/{id}', name: 'app_programme_sportif_show', methods: ['GET'])]
    public function show(ProgrammeSportif $programme, EntityManagerInterface $entityManager): Response
    {
        $latestForUser = $entityManager->getRepository(ProgrammeSportif::class)->findOneBy(
            ['user' => $programme->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('wellness/programmes/show.html.twig', [
            'programme' => $programme,
            'latest_for_user' => $latestForUser,
        ]);
    }
}
