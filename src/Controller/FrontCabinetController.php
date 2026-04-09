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

class FrontCabinetController extends AbstractController
{
    #[Route('/cabinet', name: 'front_cabinet_index')]
    public function index(Request $request, CabinetMetierService $cabinetMetier): Response
    {
        return $this->render('front/cabinet_index.html.twig', $cabinetMetier->buildListeData($request));
    }

    /* === METHODES CRUD DESACTIVEES POUR LE FRONT (RESERVEES A L'ADMIN) === */
    
    /*
    #[Route('/cabinet/new', name: 'front_cabinet_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $cabinet = new Cabinet();
        $cabinet->setStatus('actif');

        $form = $this->createForm(CabinetType::class, $cabinet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cabinet);
            $em->flush();

            $this->addFlash('success', 'Cabinet ajoute avec succes.');

            return $this->redirectToRoute('front_cabinet_index');
        }

        return $this->render('front/cabinet_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    */

    #[Route('/cabinet/{id}', name: 'front_cabinet_show')]
    public function show(Cabinet $cabinet): Response
    {
        return $this->render('front/cabinet_show.html.twig', [
            'cabinet' => $cabinet,
        ]);
    }

    /*
    #[Route('/cabinet/{id}/edit', name: 'front_cabinet_edit')]
    public function edit(Cabinet $cabinet, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CabinetType::class, $cabinet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Cabinet modifie avec succes.');

            return $this->redirectToRoute('front_cabinet_index');
        }

        return $this->render('front/cabinet_edit.html.twig', [
            'form' => $form->createView(),
            'cabinet' => $cabinet,
        ]);
    }
    */

    /*
    #[Route('/cabinet/{id}/delete', name: 'front_cabinet_delete', methods: ['POST'])]
    public function delete(Cabinet $cabinet, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_front_cabinet' . $cabinet->getIdCabinet(), $request->request->get('_token'))) {
            $conn = $em->getConnection();
            $count = (int) $conn->fetchOne(
                'SELECT COUNT(*) FROM psychologue WHERE idCabinet = :id',
                ['id' => $cabinet->getIdCabinet()]
            );

            if ($count > 0) {
                $this->addFlash(
                    'danger',
                    'Ce cabinet ne peut pas etre supprime car des psychologues y sont encore rattaches.'
                );
            } else {
                $em->remove($cabinet);
                $em->flush();
                $this->addFlash('success', 'Cabinet supprime avec succes.');
            }
        }

        return $this->redirectToRoute('front_cabinet_index');
    }
    */
}