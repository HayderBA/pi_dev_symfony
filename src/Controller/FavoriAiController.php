<?php

namespace App\Controller;

use App\Entity\Favori;
use App\Entity\Ressource;
use App\Repository\FavoriRepository;
use App\Repository\RessourceRepository;
use App\Service\GeminiService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/favori')]
class FavoriAiController extends AbstractController
{
    private GeminiService $gemini;
    private FavoriRepository $favoriRepository;
    private RessourceRepository $ressourceRepo;

    public function __construct(
        GeminiService $gemini,
        FavoriRepository $favoriRepository,
        RessourceRepository $ressourceRepo
    ) {
        $this->gemini = $gemini;
        $this->favoriRepository = $favoriRepository;
        $this->ressourceRepo = $ressourceRepo;
    }

    #[Route('/', name: 'app_favori_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $allFavoris = $this->favoriRepository->findAll();
        $allRessources = $this->ressourceRepo->findAll();
        
        $recIds = $this->gemini->recommendRessources($allFavoris, $allRessources);
        $recommendations = $this->ressourceRepo->findBy(['id' => $recIds]);

        $qb = $this->favoriRepository->createQueryBuilder('f')
            ->leftJoin('f.ressource', 'r')
            ->orderBy('f.id', 'DESC');

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('favori/index.html.twig', [
            'favoris' => $pagination,
            'recommendations' => $recommendations,
        ]);
    }
    
    #[Route('/{id}', name: 'app_favori_show', methods: ['GET'], requirements: ['id'=>'\d+'])]
    public function show(Favori $favori): Response
    {
        return $this->render('favori/show.html.twig', [
            'favori' => $favori,
        ]);
    }

    #[Route('/{id}', name: 'app_favori_delete', methods: ['POST'])]
    public function delete(Request $request, Favori $favori, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$favori->getId(), $request->request->get('_token'))) {
            $entityManager->remove($favori);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_favori_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/toggle/{id}', name: 'app_favori_toggle', methods: ['POST', 'GET'])]
    public function toggle(Ressource $ressource, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $userId = 1; // Demo
        $favori = $this->favoriRepository->findOneBy(['userId' => $userId, 'ressource' => $ressource]);

        if ($favori) {
            $entityManager->remove($favori);
        } else {
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
