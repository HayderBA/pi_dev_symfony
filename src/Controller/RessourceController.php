<?php

namespace App\Controller;

use App\Repository\EvaluationRepository;
use App\Repository\FavoriRepository;
use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use App\Service\MailerService;
use Cloudinary\Cloudinary;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ressource')]
class RessourceController extends AbstractController
{
    private function getFirstFormError(FormInterface $form): ?string
    {
        foreach ($form->getErrors(true, true) as $error) {
            return $error->getMessage();
        }

        return null;
    }

    private function getCloudinary(): Cloudinary
    {
        $cloudName = trim((string) $this->getParameter('cloudinary.cloud_name'));
        $apiKey = trim((string) $this->getParameter('cloudinary.api_key'));
        $apiSecret = trim((string) $this->getParameter('cloudinary.api_secret'));

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            throw new \RuntimeException('Configuration Cloudinary manquante. Vérifiez CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY et CLOUDINARY_API_SECRET.');
        }

        return new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => ['secure' => true],
        ]);
    }

    private function uploadToCloudinary(\Symfony\Component\HttpFoundation\File\UploadedFile $file, string $title): string
    {
        $cloudinary = $this->getCloudinary();

        $result = $cloudinary->uploadApi()->upload(
            $file->getPathname(),
            [
                'folder'          => 'growmind/ressources',
                'upload_preset'   => $this->getParameter('cloudinary.upload_preset'),
                'resource_type'   => 'raw',
                'public_id'       => 'ressource_' . uniqid(),
                'use_filename'    => false,
                'unique_filename' => true,
            ]
        );

        return (string) $result['secure_url'];
    }

    #[Route('/', name: 'app_ressource_index', methods: ['GET'])]
    public function index(Request $request, RessourceRepository $ressourceRepository, EvaluationRepository $evaluationRepository, FavoriRepository $favoriRepository, PaginatorInterface $paginator): Response
    {
        if ($request->isXmlHttpRequest()) {
            $query  = $request->query->get('q');
            $type   = $request->query->get('type');
            $status = $request->query->get('status');

            $qb = $ressourceRepository->createQueryBuilder('r');

            if ($query) {
                $qb->andWhere('r.title LIKE :query OR r.category LIKE :query OR r.author LIKE :query')
                   ->setParameter('query', '%' . $query . '%');
            }
            if ($type) {
                $qb->andWhere('r.type = :type')->setParameter('type', $type);
            }
            if ($status) {
                $qb->andWhere('r.status = :status')->setParameter('status', $status);
            }

            return $this->render('ressource/_list.html.twig', [
                'ressources' => $qb->getQuery()->getResult(),
            ]);
        }

        $allRessources = $ressourceRepository->findAll();

        $typesStats = [];
        foreach ($allRessources as $r) {
            $t = $r->getType() ?: 'Inconnu';
            $typesStats[$t] = ($typesStats[$t] ?? 0) + 1;
        }

        $stats = [
            'total'           => count($allRessources),
            'published'       => count($ressourceRepository->findBy(['status' => 'PUBLISHED'])),
            'draft'           => count($ressourceRepository->findBy(['status' => 'DRAFT'])),
            'evaluations'     => $evaluationRepository->count([]),
            'favoris'         => $favoriRepository->count([]),
            'chartTypeLabels' => array_keys($typesStats),
            'chartTypeData'   => array_values($typesStats),
        ];

        $qb         = $ressourceRepository->createQueryBuilder('r')->orderBy('r.id', 'DESC');
        $pagination = $paginator->paginate($qb, $request->query->getInt('page', 1), 5);

        return $this->render('ressource/index.html.twig', [
            'ressources' => $pagination,
            'stats'      => $stats,
        ]);
    }

    #[Route('/new', name: 'app_ressource_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerService $mailerService): Response
    {
        $ressource = new Ressource();
        if (!$ressource->getDateCreation()) {
            $ressource->setDateCreation(new \DateTime());
        }

        $form = $this->createForm(RessourceType::class, $ressource);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $pdfFile = $form->get('pdfFile')->getData();
            // Pre-fill content with a placeholder so the entity NotBlank passes
            // when the user only uploads a PDF (content field is left empty).
            if ($pdfFile && empty(trim((string) $ressource->getContent()))) {
                $ressource->setContent('PDF_PENDING');
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $pdfFile = $form->get('pdfFile')->getData();

            if ($pdfFile) {
                $extension = strtolower((string) $pdfFile->getClientOriginalExtension());
                if ($extension !== 'pdf') {
                    $this->addFlash('danger', 'Le fichier sélectionné doit être un PDF (.pdf).');
                    return $this->render('ressource/new.html.twig', [
                        'ressource' => $ressource,
                        'form'      => $form->createView(),
                    ]);
                }
                try {
                    $url = $this->uploadToCloudinary($pdfFile, $ressource->getTitle());
                    $ressource->setContent('PDF::' . $url);
                } catch (\Throwable $e) {
                    $this->addFlash('danger', 'Erreur upload Cloudinary : ' . $e->getMessage());
                    return $this->render('ressource/new.html.twig', [
                        'ressource' => $ressource,
                        'form'      => $form->createView(),
                    ]);
                }
            } elseif (empty(trim((string) $ressource->getContent()))) {
                // Neither PDF nor text content — tell the user
                $this->addFlash('danger', 'Veuillez saisir un contenu ou téléverser un fichier PDF.');
                return $this->render('ressource/new.html.twig', [
                    'ressource' => $ressource,
                    'form'      => $form->createView(),
                ]);
            }

            $entityManager->persist($ressource);
            $entityManager->flush();

            try {
                $mailerService->sendNewRessourceNotification($ressource);
            } catch (\Throwable $e) {
                $this->addFlash('warning', 'Ressource créée, mais l\'email n\'a pas pu être envoyé : ' . $e->getMessage());
            }

            $this->addFlash('success', 'Ressource créée avec succès. Un email de notification a été envoyé. ✉️');
            return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errorMessage = $this->getFirstFormError($form) ?? 'Formulaire invalide. Vérifiez les champs et le fichier PDF.';
            $this->addFlash('danger', $errorMessage);
        }

        return $this->render('ressource/new.html.twig', [
            'ressource' => $ressource,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_ressource_show', methods: ['GET'], requirements: ['id' => '\d+'])]
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

        if ($form->isSubmitted()) {
            $pdfFile = $form->get('pdfFile')->getData();
            if ($pdfFile && empty(trim((string) $ressource->getContent()))) {
                $ressource->setContent('PDF_PENDING');
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $pdfFile = $form->get('pdfFile')->getData();
            if ($pdfFile) {
                $extension = strtolower((string) $pdfFile->getClientOriginalExtension());
                if ($extension !== 'pdf') {
                    $this->addFlash('danger', 'Le fichier sélectionné doit être un PDF (.pdf).');
                    return $this->render('ressource/edit.html.twig', [
                        'ressource' => $ressource,
                        'form'      => $form->createView(),
                    ]);
                }
                try {
                    $url = $this->uploadToCloudinary($pdfFile, $ressource->getTitle());
                    $ressource->setContent('PDF::' . $url);
                } catch (\Throwable $e) {
                    $this->addFlash('danger', 'Erreur upload Cloudinary : ' . $e->getMessage());
                    return $this->render('ressource/edit.html.twig', [
                        'ressource' => $ressource,
                        'form'      => $form->createView(),
                    ]);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Ressource mise à jour avec succès.');
            return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errorMessage = $this->getFirstFormError($form) ?? 'Formulaire invalide. Vérifiez les champs et le fichier PDF.';
            $this->addFlash('danger', $errorMessage);
        }

        return $this->render('ressource/edit.html.twig', [
            'ressource' => $ressource,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{id}/download', name: 'app_ressource_download', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function download(Ressource $ressource): Response
    {
        $content = $ressource->getContent();
        if (!$content || !str_starts_with($content, 'PDF::')) {
            throw $this->createNotFoundException('Aucun fichier PDF associé à cette ressource.');
        }

        $cloudinaryUrl = substr($content, 5);
        $safeTitle = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $ressource->getTitle()) ?: 'ressource';
        $filename = sprintf('%s_%d.pdf', $safeTitle, $ressource->getId());

        try {
            $pdfBinary = @file_get_contents($cloudinaryUrl);
            if ($pdfBinary !== false) {
                return new Response(
                    $pdfBinary,
                    Response::HTTP_OK,
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
                    ]
                );
            }
        } catch (\Throwable) {
            // Fallback to Cloudinary redirect if direct fetch fails.
        }

        return new RedirectResponse($cloudinaryUrl);
    }

    #[Route('/{id}/export-pdf', name: 'app_ressource_export_pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function exportPdf(Ressource $ressource): Response
    {
        $content = (string) $ressource->getContent();
        $isCloudinaryPdf = str_starts_with($content, 'PDF::');
        $cloudinaryUrl = $isCloudinaryPdf ? substr($content, 5) : null;

        $html = $this->renderView('ressource/pdf_export.html.twig', [
            'ressource' => $ressource,
            'isCloudinaryPdf' => $isCloudinaryPdf,
            'cloudinaryUrl' => $cloudinaryUrl,
            'generatedAt' => new \DateTimeImmutable(),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeTitle = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $ressource->getTitle()) ?: 'ressource';
        $filename = sprintf('%s_%d.pdf', $safeTitle, $ressource->getId());

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }

    #[Route('/{id}', name: 'app_ressource_delete', methods: ['POST'])]
    public function delete(Request $request, Ressource $ressource, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ressource->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ressource);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
    }
}
