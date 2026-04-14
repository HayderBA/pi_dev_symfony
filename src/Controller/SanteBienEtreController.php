<?php

namespace App\Controller;

use App\Entity\SanteBienEtre;
use App\Form\SanteBienEtreType;
use App\Service\RecommendationService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sante-bien-etre/suivi')]
final class SanteBienEtreController extends AbstractController
{
    #[Route(name: 'app_sante_bien_etre_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $filters = [
            'humeur' => trim($request->query->getString('humeur')),
            'niveau_stress' => trim((string) $request->query->get('niveau_stress', '')),
            'date_suivi' => trim((string) $request->query->get('date_suivi', '')),
            'sort_by' => trim((string) $request->query->get('sort_by', 'date_suivi')),
            'sort_direction' => strtolower(trim((string) $request->query->get('sort_direction', 'desc'))),
        ];

        $queryBuilder = $entityManager
            ->getRepository(SanteBienEtre::class)
            ->createQueryBuilder('s');

        if ('' !== $filters['humeur']) {
            $queryBuilder
                ->andWhere('LOWER(s.humeur) LIKE :humeur')
                ->setParameter('humeur', '%'.strtolower($filters['humeur']).'%');
        }

        if ('' !== $filters['niveau_stress'] && is_numeric($filters['niveau_stress'])) {
            $queryBuilder
                ->andWhere('s.niveauStress = :niveauStress')
                ->setParameter('niveauStress', (int) $filters['niveau_stress']);
        }

        if ($this->isValidDate($filters['date_suivi'])) {
            $queryBuilder
                ->andWhere('s.dateSuivi = :dateSuivi')
                ->setParameter('dateSuivi', new \DateTimeImmutable($filters['date_suivi']), Types::DATE_IMMUTABLE);
        }

        $allowedSortFields = [
            'date_suivi' => 's.dateSuivi',
            'niveau_stress' => 's.niveauStress',
        ];
        $sortField = $allowedSortFields[$filters['sort_by']] ?? $allowedSortFields['date_suivi'];
        $sortDirection = in_array($filters['sort_direction'], ['asc', 'desc'], true)
            ? strtoupper($filters['sort_direction'])
            : 'DESC';

        $filters['sort_by'] = array_key_exists($filters['sort_by'], $allowedSortFields) ? $filters['sort_by'] : 'date_suivi';
        $filters['sort_direction'] = strtolower($sortDirection);

        $queryBuilder
            ->orderBy($sortField, $sortDirection)
            ->addOrderBy('s.id', 'DESC');

        $santeBienEtres = $queryBuilder
            ->getQuery()
            ->getResult();

        if ($request->isXmlHttpRequest()) {
            return $this->render('sante_bien_etre/_results.html.twig', [
                'sante_bien_etres' => $santeBienEtres,
            ]);
        }

        return $this->render('sante_bien_etre/index.html.twig', [
            'sante_bien_etres' => $santeBienEtres,
            'filters' => $filters,
        ]);
    }

    #[Route('/new', name: 'app_sante_bien_etre_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        RecommendationService $recommendationService
    ): Response
    {
        $santeBienEtre = new SanteBienEtre();
        $form = $this->createForm(SanteBienEtreType::class, $santeBienEtre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyRecommendations($santeBienEtre, $recommendationService);
            $entityManager->persist($santeBienEtre);
            $entityManager->flush();
            $this->addFlash('success', 'Health record created successfully.');

            return $this->redirectToRoute('app_sante_bien_etre_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Unable to create the health record. Please check the form fields.');
        }

        return $this->render('sante_bien_etre/new.html.twig', [
            'sante_bien_etre' => $santeBienEtre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sante_bien_etre_show', methods: ['GET'])]
    public function show(SanteBienEtre $santeBienEtre): Response
    {
        return $this->render('sante_bien_etre/show.html.twig', [
            'sante_bien_etre' => $santeBienEtre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sante_bien_etre_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        SanteBienEtre $santeBienEtre,
        EntityManagerInterface $entityManager,
        RecommendationService $recommendationService
    ): Response
    {
        $form = $this->createForm(SanteBienEtreType::class, $santeBienEtre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyRecommendations($santeBienEtre, $recommendationService);
            $entityManager->flush();
            $this->addFlash('success', 'Health record updated successfully.');

            return $this->redirectToRoute('app_sante_bien_etre_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Unable to update the health record. Please check the form fields.');
        }

        return $this->render('sante_bien_etre/edit.html.twig', [
            'sante_bien_etre' => $santeBienEtre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sante_bien_etre_delete', methods: ['POST'])]
    public function delete(Request $request, SanteBienEtre $santeBienEtre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$santeBienEtre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($santeBienEtre);
            $entityManager->flush();
            $this->addFlash('success', 'Health record deleted successfully.');
        } else {
            $this->addFlash('error', 'Unable to delete the health record. Invalid security token.');
        }

        return $this->redirectToRoute('app_sante_bien_etre_index', [], Response::HTTP_SEE_OTHER);
    }

    private function applyRecommendations(
        SanteBienEtre $santeBienEtre,
        RecommendationService $recommendationService
    ): void {
        $santeBienEtre->setRecommandations($recommendationService->generateRecommendations(
            $santeBienEtre->getNiveauStress(),
            $santeBienEtre->getQualiteSommeil(),
            $santeBienEtre->getHumeur()
        ));
    }

    private function isValidDate(string $date): bool
    {
        if ('' === $date) {
            return false;
        }

        $parsedDate = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return false !== $parsedDate && $parsedDate->format('Y-m-d') === $date;
    }
}
