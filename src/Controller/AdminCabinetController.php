<?php

namespace App\Controller;

use App\Entity\Cabinet;
use App\Form\CabinetType;
use App\Service\CabinetMetierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/cabinet')]
class AdminCabinetController extends AbstractController
{
    #[Route('/', name: 'admin_cabinet_index')]
    public function index(Request $request, CabinetMetierService $cabinetMetier): Response
    {
        return $this->render('back/cabinet_index.html.twig', $cabinetMetier->buildListeData($request));
    }

    #[Route('/new', name: 'admin_cabinet_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $cabinet = new Cabinet();
        $cabinet->setStatus('actif');
        
        $form = $this->createForm(CabinetType::class, $cabinet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cabinet);
            $em->flush();
            $this->addFlash('success', 'Cabinet créé avec succès');
            return $this->redirectToRoute('admin_cabinet_index');
        }

        return $this->render('back/cabinet_new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_cabinet_edit')]
    public function edit(Cabinet $cabinet, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CabinetType::class, $cabinet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Cabinet modifié avec succès');
            return $this->redirectToRoute('admin_cabinet_index');
        }

        return $this->render('back/cabinet_edit.html.twig', [
            'form' => $form->createView(),
            'cabinet' => $cabinet
        ]);
    }

    #[Route('/{id}', name: 'admin_cabinet_show')]
    public function show(Cabinet $cabinet): Response
    {
        return $this->render('back/cabinet_show.html.twig', [
            'cabinet' => $cabinet
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_cabinet_delete', methods: ['POST'])]
    public function delete(Cabinet $cabinet, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cabinet->getIdCabinet(), $request->request->get('_token'))) {
            // Verifier si au moins un psychologue est encore lie a ce cabinet
            $conn = $em->getConnection();
            $count = (int) $conn->fetchOne(
                'SELECT COUNT(*) FROM psychologue WHERE idCabinet = :id',
                ['id' => $cabinet->getIdCabinet()]
            );

            if ($count > 0) {
                $this->addFlash(
                    'danger',
                    'Impossible de supprimer ce cabinet car au moins un psychologue y est encore associe.'
                );
            } else {
                $em->remove($cabinet);
                $em->flush();
                $this->addFlash('success', 'Cabinet supprimé');
            }
        }
        return $this->redirectToRoute('admin_cabinet_index');
    }
}