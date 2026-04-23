<?php

namespace App\Controller;

use App\Entity\SanteBienEtre;
use App\Form\SanteBienEtreType;
use App\Service\RecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bien-etre/suivi')]
final class SanteBienEtreController extends AbstractController
{
    #[Route('/', name: 'app_sante_bien_etre_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $filters = [
            'humeur' => trim((string) $request->query->get('humeur', '')),
            'niveau' => trim((string) $request->query->get('niveau', '')),
        ];

        $qb = $entityManager->getRepository(SanteBienEtre::class)->createQueryBuilder('s');

        if ('' !== $filters['humeur']) {
            $qb->andWhere('LOWER(s.humeur) LIKE :humeur')->setParameter('humeur', '%'.strtolower($filters['humeur']).'%');
        }

        if ('' !== $filters['niveau'] && is_numeric($filters['niveau'])) {
            $qb->andWhere('s.niveauStress = :niveau')->setParameter('niveau', (int) $filters['niveau']);
        }

        $records = $qb->orderBy('s.dateSuivi', 'DESC')->addOrderBy('s.id', 'DESC')->getQuery()->getResult();

        return $this->render('wellness/suivi/index.html.twig', [
            'records' => $records,
            'filters' => $filters,
        ]);
    }

    #[Route('/new', name: 'app_sante_bien_etre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, RecommendationService $recommendationService): Response
    {
        $record = new SanteBienEtre();
        $form = $this->createForm(SanteBienEtreType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $record->setRecommandations($recommendationService->generateRecommendations(
                $record->getNiveauStress(),
                $record->getQualiteSommeil(),
                $record->getHumeur()
            ));
            $entityManager->persist($record);
            $entityManager->flush();
            $this->addFlash('success', 'Suivi bien-etre ajoute avec succes.');

            return $this->redirectToRoute('app_sante_bien_etre_index');
        }

        return $this->render('wellness/suivi/form.html.twig', [
            'title' => 'Nouveau suivi bien-etre',
            'form' => $form,
            'record' => $record,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sante_bien_etre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SanteBienEtre $record, EntityManagerInterface $entityManager, RecommendationService $recommendationService): Response
    {
        $form = $this->createForm(SanteBienEtreType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $record->setRecommandations($recommendationService->generateRecommendations(
                $record->getNiveauStress(),
                $record->getQualiteSommeil(),
                $record->getHumeur()
            ));
            $entityManager->flush();
            $this->addFlash('success', 'Suivi bien-etre mis a jour.');

            return $this->redirectToRoute('app_sante_bien_etre_index');
        }

        return $this->render('wellness/suivi/form.html.twig', [
            'title' => 'Modifier le suivi bien-etre',
            'form' => $form,
            'record' => $record,
        ]);
    }

    #[Route('/{id}', name: 'app_sante_bien_etre_delete', methods: ['POST'])]
    public function delete(Request $request, SanteBienEtre $record, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_sante_'.$record->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($record);
            $entityManager->flush();
            $this->addFlash('success', 'Suivi bien-etre supprime.');
        }

        return $this->redirectToRoute('app_sante_bien_etre_index');
    }
}
