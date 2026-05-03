<?php

namespace App\Controller;

use App\Repository\EvaluationRepository;
use App\Repository\FavoriRepository;
use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use App\Service\MailerService;
use App\Service\GeminiService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/ressource')]
class RessourceAdminController extends AbstractController
{
    private GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    #[Route('/', name: 'app_ressource_index', methods: ['GET', 'POST'])]
    public function index(Request $request, RessourceRepository $ressourceRepository, EvaluationRepository $evaluationRepository, FavoriRepository $favoriRepository, PaginatorInterface $paginator): Response
    {
        // Stats
        $all = $ressourceRepository->findAll();
        $typesCount = [];
        foreach ($all as $r) {
            $type = $r->getType() ?? 'Autre';
            if (!isset($typesCount[$type])) {
                $typesCount[$type] = 0;
            }
            $typesCount[$type]++;
        }

        $statsData = [
            'total' => count($all),
            'published' => count($ressourceRepository->findBy(['status' => 'PUBLISHED'])),
            'draft' => count($ressourceRepository->findBy(['status' => 'DRAFT'])),
            'evaluations' => $evaluationRepository->count([]),
            'favoris' => $favoriRepository->count([]),
            'chartTypeLabels' => array_keys($typesCount),
            'chartTypeData' => array_values($typesCount)
        ];

        if ($request->isXmlHttpRequest()) {
            $qb = $ressourceRepository->createQueryBuilder('r');
            $query = $request->query->get('q');
            $type = $request->query->get('type');
            $status = $request->query->get('status');
            
            if ($query) { $qb->andWhere('r.title LIKE :q OR r.category LIKE :q OR r.author LIKE :q')->setParameter('q', "%$query%"); }
            if ($type) { $qb->andWhere('r.type = :type')->setParameter('type', $type); }
            if ($status) { $qb->andWhere('r.status = :status')->setParameter('status', $status); }
            
            return $this->render('ressource/_list.html.twig', ['ressources' => $qb->getQuery()->getResult()]);
        }

        $qb = $ressourceRepository->createQueryBuilder('r')->orderBy('r.id', 'DESC');
        $pagination = $paginator->paginate($qb, $request->query->getInt('page', 1), 5);

        return $this->render('ressource/index.html.twig', [
            'ressources' => $pagination,
            'stats' => $statsData,
            'dashboardStats' => $statsData
        ]);
    }

    #[Route('/new', name: 'app_ressource_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ressource = new Ressource();
        $ressource->setDateCreation(new \DateTime());
        $form = $this->createForm(RessourceType::class, $ressource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ressource);
            $entityManager->flush();
            return $this->redirectToRoute('app_ressource_index');
        }
        return $this->render('ressource/new.html.twig', ['ressource' => $ressource, 'form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_ressource_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ressource $ressource, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RessourceType::class, $ressource);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_ressource_index');
        }
        return $this->render('ressource/edit.html.twig', ['ressource' => $ressource, 'form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'app_ressource_delete', methods: ['POST'])]
    public function delete(Request $request, Ressource $ressource, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ressource->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ressource);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_ressource_index');
    }

    #[Route('/{id}/show', name: 'app_ressource_show', methods: ['GET'])]
    public function show(Ressource $ressource): Response
    {
        return $this->render('ressource/show.html.twig', [
            'ressource' => $ressource,
        ]);
    }

    #[Route('/{id}/qr-view', name: 'app_ressource_qr_view', methods: ['GET'])]
    public function qrView(Ressource $ressource): Response
    {
        $content = (string) $ressource->getContent();
        $isCloudinaryPdf = str_starts_with($content, 'PDF::');
        $cloudinaryUrl = $isCloudinaryPdf ? substr($content, 5) : null;

        return $this->render('ressource/pdf_export.html.twig', [
            'ressource' => $ressource,
            'generatedAt' => new \DateTimeImmutable(),
            'isCloudinaryPdf' => $isCloudinaryPdf,
            'cloudinaryUrl' => $cloudinaryUrl,
        ]);
    }

    #[Route('/{id}/export-pdf', name: 'app_ressource_export_pdf', methods: ['GET'])]
    public function exportPdf(Ressource $ressource): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $content = (string) $ressource->getContent();
        $isCloudinaryPdf = str_starts_with($content, 'PDF::');
        $cloudinaryUrl = $isCloudinaryPdf ? substr($content, 5) : null;

        $html = $this->renderView('ressource/pdf_export.html.twig', [
            'ressource' => $ressource,
            'generatedAt' => new \DateTimeImmutable(),
            'isCloudinaryPdf' => $isCloudinaryPdf,
            'cloudinaryUrl' => $cloudinaryUrl,
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ressource_'.$ressource->getId().'.pdf"'
        ]);
    }

    #[Route('/{id}/download', name: 'app_ressource_download', methods: ['GET'])]
    public function download(Ressource $ressource): Response
    {
        $content = (string) $ressource->getContent();
        if (str_starts_with($content, 'PDF::')) {
            $url = substr($content, 5);
            return $this->redirect($url);
        }
        return $this->redirectToRoute('app_ressource_index');
    }
}
