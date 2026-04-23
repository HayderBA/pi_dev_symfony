<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Form\EvaluationType;
use App\Repository\EvaluationRepository;
use App\Repository\RessourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/evaluation')]
class EvaluationController extends AbstractController
{
    #[Route('/', name: 'app_evaluation_index', methods: ['GET'])]
    public function index(Request $request, EvaluationRepository $evaluationRepository, PaginatorInterface $paginator): Response
    {
        if ($request->isXmlHttpRequest()) {
            $query = $request->query->get('q');
            $note = $request->query->get('note');

            $qb = $evaluationRepository->createQueryBuilder('e')
                ->join('e.ressource', 'r');

            if ($query) {
                $qb->andWhere('r.title LIKE :query OR e.commentaire LIKE :query')
                   ->setParameter('query', '%' . $query . '%');
            }
            if ($note) {
                $qb->andWhere('e.note = :note')
                   ->setParameter('note', $note);
            }

            return $this->render('evaluation/_list.html.twig', [
                'evaluations' => $qb->getQuery()->getResult(),
            ]);
        }

        $allEvals = $evaluationRepository->findAll();

        $notesStats = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($allEvals as $e) {
            $n = $e->getNote();
            if (isset($notesStats[$n])) $notesStats[$n]++;
        }

        $qb = $evaluationRepository->createQueryBuilder('e')
            ->leftJoin('e.ressource', 'r')
            ->orderBy('e.id', 'DESC');

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('evaluation/index.html.twig', [
            'evaluations' => $pagination,
            'stats' => [
                'total' => count($allEvals),
                'chartNoteLabels' => ['⭐', '⭐⭐', '⭐⭐⭐', '⭐⭐⭐⭐', '⭐⭐⭐⭐⭐'],
                'chartNoteData' => array_values($notesStats),
            ]
        ]);
    }

    #[Route('/new', name: 'app_evaluation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, RessourceRepository $ressourceRepository): Response
    {
        $evaluation = new Evaluation();
        $form = $this->createForm(EvaluationType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Manually set ressource if provided in raw POST (from Modal)
            $formData = $request->request->all('evaluation');
            if (isset($formData['ressource'])) {
                $ressource = $ressourceRepository->find($formData['ressource']);
                if ($ressource) {
                    $evaluation->setRessource($ressource);
                }
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$evaluation->getDateEvaluation()) {
                $evaluation->setDateEvaluation(new \DateTime());
            }
            if (!$evaluation->getUserId()) {
                $evaluation->setUserId(1); // Default for demo
            }
            $entityManager->persist($evaluation);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre avis !');

            if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/admin/patient')) {
                return $this->redirect($request->headers->get('referer'));
            }

            return $this->redirectToRoute('app_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        // Handle validation errors for Modal submission
        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
            if ($request->headers->get('referer')) {
                return $this->redirect($request->headers->get('referer'));
            }
        }

        return $this->render('evaluation/new.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_evaluation_show', methods: ['GET'], requirements: ['id'=>'\d+'])]
    public function show(Evaluation $evaluation): Response
    {
        return $this->render('evaluation/show.html.twig', [
            'evaluation' => $evaluation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evaluation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvaluationType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/admin/patient')) {
                return $this->redirect($request->headers->get('referer'));
            }

            return $this->redirectToRoute('app_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evaluation/edit.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_evaluation_delete', methods: ['POST'])]
    public function delete(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evaluation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evaluation);
            $entityManager->flush();
        }

        if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/admin/patient')) {
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirectToRoute('app_evaluation_index', [], Response::HTTP_SEE_OTHER);
    }
}
