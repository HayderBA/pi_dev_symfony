<?php

namespace App\Controller;

use App\Repository\EvaluationRepository;
use App\Repository\FavoriRepository;
use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ressource')]
class RessourceController extends AbstractController
{
    #[Route('/', name: 'app_ressource_index', methods: ['GET'])]
    public function index(Request $request, RessourceRepository $ressourceRepository, EvaluationRepository $evaluationRepository, FavoriRepository $favoriRepository): Response
    {
        if ($request->isXmlHttpRequest()) {
            $query = $request->query->get('q');
            $type = $request->query->get('type');
            $status = $request->query->get('status');

            $qb = $ressourceRepository->createQueryBuilder('r');

            if ($query) {
                $qb->andWhere('r.title LIKE :query OR r.category LIKE :query OR r.author LIKE :query')
                   ->setParameter('query', '%' . $query . '%');
            }
            if ($type) {
                $qb->andWhere('r.type = :type')
                   ->setParameter('type', $type);
            }
            if ($status) {
                $qb->andWhere('r.status = :status')
                   ->setParameter('status', $status);
            }

            return $this->render('ressource/_list.html.twig', [
                'ressources' => $qb->getQuery()->getResult(),
            ]);
        }
        
        $allRessources = $ressourceRepository->findAll();
        
        // Distribution categories for charts
        $typesStats = [];
        foreach ($allRessources as $r) {
            $type = $r->getType() ?: 'Inconnu';
            $typesStats[$type] = ($typesStats[$type] ?? 0) + 1;
        }

        $stats = [
            'total' => count($allRessources),
            'published' => count($ressourceRepository->findBy(['status' => 'PUBLISHED'])),
            'draft' => count($ressourceRepository->findBy(['status' => 'DRAFT'])),
            'evaluations' => $evaluationRepository->count([]),
            'favoris' => $favoriRepository->count([]),
            'chartTypeLabels' => array_keys($typesStats),
            'chartTypeData' => array_values($typesStats),
        ];

        return $this->render('ressource/index.html.twig', [
            'ressources' => $allRessources,
            'stats' => $stats,
        ]);
    }

    #[Route('/new', name: 'app_ressource_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ressource = new Ressource();
        if (!$ressource->getDateCreation()) {
            $ressource->setDateCreation(new \DateTime());
        }

        $form = $this->createForm(RessourceType::class, $ressource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ressource);
            $entityManager->flush();

            return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ressource/new.html.twig', [
            'ressource' => $ressource,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_ressource_show', methods: ['GET'], requirements: ['id'=>'\d+'])]
    public function show(Ressource $ressource): Response
    {
        return $this->render('ressource/show.html.twig', [
            'ressource' => $ressource,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ressource_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ressource $ressource, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RessourceType::class, $ressource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ressource/edit.html.twig', [
            'ressource' => $ressource,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_ressource_delete', methods: ['POST'])]
    public function delete(Request $request, Ressource $ressource, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ressource->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ressource);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
    }
}
