<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Entity\Reponse;
use App\Entity\User;
use App\Form\ForumPostType;
use App\Form\ReponseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'forum_index')]
    #[Route('/forum/categorie/{categorie}', name: 'forum_categorie')]
    public function index(Request $request, EntityManagerInterface $em, ?string $categorie = null): Response
    {
        $query = $request->query->get('q', '');
        $repo = $em->getRepository(ForumPost::class);
        $qb = $repo->createQueryBuilder('p')
            ->where('p.archive = false');

        if ($query !== '') {
            $qb->andWhere('p.nom LIKE :q OR p.contenu LIKE :q OR p.categorie LIKE :q')
               ->setParameter('q', '%' . $query . '%');
        }
        if ($categorie) {
            $qb->andWhere('p.categorie = :cat')->setParameter('cat', $categorie);
        }
        $posts = $qb->orderBy('p.dateCreation', 'DESC')->getQuery()->getResult();

        // ========== RÉCUPÉRATION DES MÉDECINS POUR LA CARTE ==========
        $userRepo = $em->getRepository(User::class);
        $medecins = $userRepo->findBy(['role' => 'medecin']);
        
        $medecinsData = [];
        foreach ($medecins as $medecin) {
            $medecinsData[] = [
                'id' => $medecin->getId(),
                'nom' => $medecin->getName() . ' ' . $medecin->getSecondName(),
                'latitude' => $medecin->getLatitude() ?? 36.8065,
                'longitude' => $medecin->getLongitude() ?? 10.1815,
                'adresse' => $medecin->getAdresse() ?? 'Tunis, Tunisie'
            ];
        }

        // ========== ZONES D'ALERTE DYNAMIQUES ==========
        // Basées sur les posts contenant des mots-clés d'urgence
        $zonesAlerte = [];
        $motsUrgence = ['urgence', 'crise', 'suicide', 'mourir', 'panique', 'angoisse', 'détresse'];
        
        foreach ($posts as $post) {
            $contenu = strtolower($post->getContenu());
            foreach ($motsUrgence as $mot) {
                if (strpos($contenu, $mot) !== false) {
                    // Créer une zone d'alerte autour de ce post (coordonnées par défaut)
                    $zonesAlerte[] = [
                        'nom' => 'Zone ' . $post->getCategorie(),
                        'latitude' => 36.8065 + (rand(0, 100) / 1000),
                        'longitude' => 10.1815 + (rand(0, 100) / 1000),
                        'rayon' => 2000,
                        'niveau' => 'moyen'
                    ];
                    break;
                }
            }
        }
        
        // Supprimer les doublons de zones
        $zonesAlerte = array_unique($zonesAlerte, SORT_REGULAR);
        
        // Si aucune zone d'alerte trouvée, ajouter une zone par défaut
        if (empty($zonesAlerte)) {
            $zonesAlerte = [
                [
                    'nom' => 'Centre Tunis',
                    'latitude' => 36.8065,
                    'longitude' => 10.1815,
                    'rayon' => 3000,
                    'niveau' => 'élevé'
                ]
            ];
        }

        // Calculer le nombre de posts urgents
        $urgentCount = 0;
        foreach ($posts as $post) {
            $contenu = strtolower($post->getContenu());
            foreach ($motsUrgence as $mot) {
                if (strpos($contenu, $mot) !== false) {
                    $urgentCount++;
                    break;
                }
            }
        }

        return $this->render('forum/index.html.twig', [
            'posts'          => $posts,
            'searchQuery'    => $query,
            'currentCategorie' => $categorie,
            'totalPosts'     => count($posts),
            'urgentPosts'    => $urgentCount,
            'medecinsData'   => $medecinsData,
            'zonesAlerte'    => $zonesAlerte
        ]);
    }

    #[Route('/forum/new', name: 'forum_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $post = new ForumPost();
        $post->setDateCreation(new \DateTimeImmutable());
        $post->setArchive(false);
        $post->setLikes(0);
        $post->setVues(0);
        $post->setDislikes(0);

        $form = $this->createForm(ForumPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'Discussion créée avec succès');
            return $this->redirectToRoute('forum_show', ['id' => $post->getId()]);
        }

        return $this->render('forum/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/forum/{id}', name: 'forum_show')]
    public function show($id, Request $request, EntityManagerInterface $em): Response
    {
        $post = $em->getRepository(ForumPost::class)->find($id);
        if (!$post) {
            $this->addFlash('error', 'Discussion introuvable');
            return $this->redirectToRoute('forum_index');
        }

        $post->setVues($post->getVues() + 1);
        $em->flush();

        $reponse = new Reponse();
        $reponse->setDateCreation(new \DateTimeImmutable());
        $reponse->setLikes(0);
        $reponse->setDislikes(0);
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setForumPost($post);
            $em->persist($reponse);
            $em->flush();
            $this->addFlash('success', 'Réponse ajoutée avec succès');
            return $this->redirectToRoute('forum_show', ['id' => $post->getId()]);
        }

        return $this->render('forum/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/forum/{id}/edit', name: 'forum_edit')]
    public function edit(Request $request, ForumPost $post, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ForumPostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Discussion modifiée');
            return $this->redirectToRoute('forum_show', ['id' => $post->getId()]);
        }
        return $this->render('forum/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post
        ]);
    }

    #[Route('/forum/{id}/delete', name: 'forum_delete', methods: ['POST'])]
    public function delete(Request $request, ForumPost $post, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
            $this->addFlash('success', 'Discussion supprimée');
        }
        return $this->redirectToRoute('forum_index');
    }
}