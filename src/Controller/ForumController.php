<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Entity\Reponse;
use App\Form\ForumPostType;
use App\Form\ReponseType;
use App\Repository\ForumPostRepository;
use App\Repository\UserRepository;
use App\Service\BadWordFilter;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/forum')]
class ForumController extends AbstractController
{
    #[Route('', name: 'app_forum_index')]
    public function index(Request $request, ForumPostRepository $forumPostRepository, UserRepository $userRepository, UrlGeneratorInterface $urlGenerator): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $categorie = trim((string) $request->query->get('categorie', ''));
        $posts = $forumPostRepository->findPublicPosts($search, $categorie);
        $medecins = $userRepository->findByRole('medecin');
        $patient = $userRepository->findAnyPatient() ?? $userRepository->findAnyMedecin();

        $medecinsData = array_map(static function ($medecin): array {
            return [
                'id' => $medecin->getId(),
                'nom' => $medecin->getFullName(),
                'latitude' => $medecin->getLatitude() ?? 36.8065,
                'longitude' => $medecin->getLongitude() ?? 10.1815,
                'adresse' => $medecin->getAdresse() ?? 'Tunis, Tunisie',
            ];
        }, $medecins);

        $zonesAlerte = [];
        $urgentCount = 0;
        foreach ($posts as $index => $post) {
            $contenu = mb_strtolower((string) $post->getContenu());
            if (str_contains($contenu, 'urgence') || str_contains($contenu, 'suicide') || str_contains($contenu, 'mourir') || str_contains($contenu, 'detresse') || str_contains($contenu, 'détresse')) {
                ++$urgentCount;
                $zonesAlerte[] = [
                    'nom' => 'Zone ' . ($post->getCategorie() ?? 'Forum'),
                    'latitude' => 36.8065 + (($index + 1) / 1000),
                    'longitude' => 10.1815 + (($index + 1) / 1000),
                    'rayon' => 2200,
                    'niveau' => 'eleve',
                ];
            }
        }

        if ($zonesAlerte === []) {
            $zonesAlerte[] = [
                'nom' => 'Centre Tunis',
                'latitude' => 36.8065,
                'longitude' => 10.1815,
                'rayon' => 3000,
                'niveau' => 'moyen',
            ];
        }

        $resources = [
            [
                'title' => 'Meditation guidee',
                'description' => 'Une courte seance pour retrouver le calme et ralentir le rythme.',
                'url' => 'https://www.youtube.com/watch?v=inpok4MKVLM',
            ],
            [
                'title' => 'Gestion du sommeil',
                'description' => 'Des conseils simples pour mieux dormir et reduire la fatigue mentale.',
                'url' => 'https://www.youtube.com/watch?v=ZToicYcHIOU',
            ],
            [
                'title' => 'Respiration anti-stress',
                'description' => 'Une routine rapide pour faire baisser la tension avant qu elle monte.',
                'url' => 'https://www.youtube.com/watch?v=1vx8iUvfyCY',
            ],
        ];

        $feedbackUserId = $patient?->getId() ?? 1;
        $feedbackUrl = $urlGenerator->generate('feedback_form', ['userId' => $feedbackUserId], UrlGeneratorInterface::ABSOLUTE_URL);
        $feedbackQrCode = (new SvgWriter())->write(new QrCode($feedbackUrl))->getString();

        return $this->render('forum/index.html.twig', [
            'posts' => $posts,
            'categories' => $forumPostRepository->findCategories(),
            'searchQuery' => $search,
            'currentCategorie' => $categorie,
            'medecinsData' => $medecinsData,
            'zonesAlerte' => $zonesAlerte,
            'urgentPosts' => $urgentCount,
            'feedbackUserId' => $feedbackUserId,
            'feedbackQrCode' => $feedbackQrCode,
            'forumResources' => $resources,
        ]);
    }

    #[Route('/new', name: 'app_forum_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, BadWordFilter $badWordFilter): Response
    {
        $post = new ForumPost();
        $form = $this->createForm(ForumPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setNom($badWordFilter->filter((string) $post->getNom()));
            $post->setContenu($badWordFilter->filter((string) $post->getContenu()));
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'La discussion a ete ajoutee avec succes.');

            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        return $this->render('forum/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_forum_show', requirements: ['id' => '\d+'])]
    public function show(ForumPost $post, Request $request, EntityManagerInterface $entityManager, BadWordFilter $badWordFilter): Response
    {
        $post->setVues($post->getVues() + 1);
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setAuteur($badWordFilter->filter((string) $reponse->getAuteur()));
            $reponse->setContenu($badWordFilter->filter((string) $reponse->getContenu()));
            $reponse->setForumPost($post);
            $entityManager->persist($reponse);
            $entityManager->flush();

            $this->addFlash('success', 'Votre reponse a ete ajoutee.');

            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        $entityManager->flush();

        return $this->render('forum/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_forum_edit', requirements: ['id' => '\d+'])]
    public function edit(ForumPost $post, Request $request, EntityManagerInterface $entityManager, BadWordFilter $badWordFilter): Response
    {
        $form = $this->createForm(ForumPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setNom($badWordFilter->filter((string) $post->getNom()));
            $post->setContenu($badWordFilter->filter((string) $post->getContenu()));
            $entityManager->flush();

            $this->addFlash('success', 'La discussion a ete modifiee.');

            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()]);
        }

        return $this->render('forum/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_forum_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(ForumPost $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_forum_' . $post->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'La discussion a ete supprimee.');
        }

        return $this->redirectToRoute('app_forum_index');
    }

    #[Route('/admin/list', name: 'app_forum_admin')]
    public function admin(ForumPostRepository $forumPostRepository): Response
    {
        return $this->render('forum/admin.html.twig', [
            'posts' => $forumPostRepository->findBy([], ['dateCreation' => 'DESC']),
        ]);
    }

    #[Route('/admin/{id}/toggle-archive', name: 'app_forum_toggle_archive', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleArchive(ForumPost $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_archive_' . $post->getId(), (string) $request->request->get('_token'))) {
            $post->setArchive(!$post->isArchive());
            $entityManager->flush();
            $this->addFlash('success', 'Le statut de la discussion a ete mis a jour.');
        }

        return $this->redirectToRoute('app_forum_admin');
    }
}
