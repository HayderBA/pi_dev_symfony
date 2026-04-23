<?php

namespace App\Controller;

use App\Entity\Cabinet;
use App\Form\CabinetType;
use App\Service\CabinetMetierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/admin/cabinets')]
class AdminCabinetController extends AbstractController
{
    #[Route('/', name: 'admin_cabinet_index')]
    public function index(Request $request, CabinetMetierService $cabinetMetier): Response
    {
        return $this->render('back/cabinet_index.html.twig', $cabinetMetier->buildListeData($request));
    }

    #[Route('/new', name: 'admin_cabinet_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cabinet = (new Cabinet())->setStatus('actif');
        $form = $this->createForm(CabinetType::class, $cabinet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cabinet);
            $entityManager->flush();
            $this->addFlash('success', 'Cabinet cree avec succes.');

            return $this->redirectToRoute('admin_cabinet_index');
        }

        return $this->render('back/cabinet_new.html.twig', ['form' => $form->createView(), 'cabinet' => $cabinet, 'is_edit' => false]);
    }

    #[Route('/{id}/edit', name: 'admin_cabinet_edit')]
    public function edit(Cabinet $cabinet, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CabinetType::class, $cabinet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Cabinet modifie avec succes.');

            return $this->redirectToRoute('admin_cabinet_index');
        }

        return $this->render('back/cabinet_edit.html.twig', ['form' => $form->createView(), 'cabinet' => $cabinet, 'is_edit' => true]);
    }

    #[Route('/{id}', name: 'admin_cabinet_show')]
    public function show(Cabinet $cabinet): Response
    {
        return $this->render('back/cabinet_show.html.twig', ['cabinet' => $cabinet]);
    }

    #[Route('/{id}/delete', name: 'admin_cabinet_delete', methods: ['POST'])]
    public function delete(Cabinet $cabinet, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cabinet->getIdCabinet(), (string) $request->request->get('_token'))) {
            if ($cabinet->getPsychologuesCount() > 0) {
                $this->addFlash('danger', 'Impossible de supprimer ce cabinet car des psychologues y sont encore associes.');
            } else {
                $entityManager->remove($cabinet);
                $entityManager->flush();
                $this->addFlash('success', 'Cabinet supprime.');
            }
        }

        return $this->redirectToRoute('admin_cabinet_index');
    }
}
