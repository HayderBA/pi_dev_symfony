<?php

namespace App\Controller;

use App\Entity\Favori;
use App\Entity\Ressource;
use App\Form\FavoriType;
use App\Repository\FavoriRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/favori')]
class FavoriController extends AbstractController
{
    #[Route('/', name: 'app_favori_index', methods: ['GET'])]
    public function index(Request $request, FavoriRepository $favoriRepository, PaginatorInterface $paginator): Response
    {
        $qb = $favoriRepository->createQueryBuilder('f')
            ->leftJoin('f.ressource', 'r')
            ->orderBy('f.id', 'DESC');

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('favori/index.html.twig', [
            'favoris' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_favori_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $favori = new Favori();
        if (!$favori->getDateAjout()) {
            $favori->setDateAjout(new \DateTime());
        }

        $form = $this->createForm(FavoriType::class, $favori);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($favori);
            $entityManager->flush();

            return $this->redirectToRoute('app_favori_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('favori/new.html.twig', [
            'favori' => $favori,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_favori_show', methods: ['GET'], requirements: ['id'=>'\d+'])]
    public function show(Favori $favori): Response
    {
        return $this->render('favori/show.html.twig', [
            'favori' => $favori,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_favori_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Favori $favori, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FavoriType::class, $favori);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_favori_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('favori/edit.html.twig', [
            'favori' => $favori,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_favori_delete', methods: ['POST'])]
    public function delete(Request $request, Favori $favori, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$favori->getId(), $request->request->get('_token'))) {
            $entityManager->remove($favori);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_favori_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/toggle/{id}', name: 'app_favori_toggle', methods: ['POST', 'GET'])]
    public function toggle(Ressource $ressource, FavoriRepository $favoriRepository, EntityManagerInterface $entityManager): Response
    {
        $userId = 1; // Demo
        $favori = $favoriRepository->findOneBy(['userId' => $userId, 'ressource' => $ressource]);

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

        return $this->redirectToRoute('app_patient_ressources');
    }
}
