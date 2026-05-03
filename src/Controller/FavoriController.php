<?php

namespace App\Controller;

use App\Entity\Favori;
use App\Entity\Ressource;
use App\Form\FavoriType;
use App\Repository\FavoriRepository;
use App\Repository\RessourceRepository;
use App\Service\GeminiService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/favori')]
class FavoriController extends AbstractController
{
    private GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    #[Route('/', name: 'app_favori_index', methods: ['GET'])]
    public function index(Request $request, FavoriRepository $favoriRepository, PaginatorInterface $paginator): Response
    {
        $qb = $favoriRepository->createQueryBuilder('f')
            ->leftJoin('f.ressource', 'r')
            ->orderBy('f.id', 'DESC');

        $pagination = $paginator->paginate($qb, $request->query->getInt('page', 1), 5);

        return $this->render('favori/index.html.twig', [
            'favoris' => $pagination,
        ]);
    }

    // 🤖 New Separate AI Endpoint (Ajax)
    #[Route('/api/recommendations', name: 'app_favori_recommendations', methods: ['GET'])]
    public function getRecommendations(FavoriRepository $favoriRepo, RessourceRepository $ressourceRepo): JsonResponse
    {
        try {
            $allFavoris = $favoriRepo->findAll();
            $allRessources = $ressourceRepo->findAll();
            $recIds = $this->geminiService->recommendRessources($allFavoris, $allRessources);
            $recommendations = $ressourceRepo->findBy(['id' => $recIds]);

            $data = [];
            foreach ($recommendations as $r) {
                $data[] = [
                    'id' => $r->getId(),
                    'title' => $r->getTitle(),
                    'category' => $r->getCategory(),
                    'content' => substr((string)$r->getContent(), 0, 80) . '...',
                    'url' => $this->generateUrl('app_ressource_show', ['id' => $r->getId()])
                ];
            }

            return new JsonResponse(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[Route('/{id}', name: 'app_favori_show', methods: ['GET'], requirements: ['id'=>'\d+'])]
    public function show(Favori $favori): Response
    {
        return $this->render('favori/show.html.twig', [
            'favori' => $favori,
        ]);
    }

    #[Route('/toggle/{id}', name: 'app_favori_toggle', methods: ['POST', 'GET'])]
    public function toggle(Ressource $ressource, FavoriRepository $favoriRepository, EntityManagerInterface $entityManager): Response
    {
        $userId = 1; // Demo
        $favori = $favoriRepository->findOneBy(['userId' => $userId, 'ressource' => $ressource]);
        if ($favori) { $entityManager->remove($favori); }
        else {
            $favori = new Favori();
            $favori->setUserId($userId);
            $favori->setRessource($ressource);
            $favori->setDateAjout(new \DateTime());
            $entityManager->persist($favori);
        }
        $entityManager->flush();
        return $this->redirectToRoute('app_admin_dashboard');
    }
}
