<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Entity\Reponse;
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

        return $this->render('forum/index.html.twig', [
            'posts'          => $posts,
            'searchQuery'    => $query,
            'currentCategorie' => $categorie,
            'totalPosts'     => count($posts),
            'urgentPosts'    => 0,
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

        // Incrémenter les vues
        $post->setVues($post->getVues() + 1);
        $em->flush();

        // Formulaire de réponse
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