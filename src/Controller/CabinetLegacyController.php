<?php

namespace App\Controller;

use App\Entity\Cabinet;
use App\Entity\Psychologue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class CabinetLegacyController extends AbstractController
{
    #[Route('/admin/cabinet', name: 'admin_cabinet_index_legacy', methods: ['GET'])]
    public function adminCabinetIndex(): RedirectResponse
    {
        return $this->redirectToRoute('admin_cabinet_index');
    }

    #[Route('/admin/cabinet/new', name: 'admin_cabinet_new_legacy', methods: ['GET'])]
    public function adminCabinetNew(): RedirectResponse
    {
        return $this->redirectToRoute('admin_cabinet_new');
    }

    #[Route('/admin/cabinet/{id}', name: 'admin_cabinet_show_legacy', methods: ['GET'])]
    public function adminCabinetShow(Cabinet $cabinet): RedirectResponse
    {
        return $this->redirectToRoute('admin_cabinet_show', ['id' => $cabinet->getIdCabinet()]);
    }

    #[Route('/admin/cabinet/{id}/edit', name: 'admin_cabinet_edit_legacy', methods: ['GET'])]
    public function adminCabinetEdit(Cabinet $cabinet): RedirectResponse
    {
        return $this->redirectToRoute('admin_cabinet_edit', ['id' => $cabinet->getIdCabinet()]);
    }

    #[Route('/admin/psychologue', name: 'admin_psychologue_index_legacy', methods: ['GET'])]
    public function adminPsychologueIndex(): RedirectResponse
    {
        return $this->redirectToRoute('admin_psychologue_index');
    }

    #[Route('/admin/psychologue/new', name: 'admin_psychologue_new_legacy', methods: ['GET'])]
    public function adminPsychologueNew(): RedirectResponse
    {
        return $this->redirectToRoute('admin_psychologue_new');
    }

    #[Route('/admin/psychologue/{id}', name: 'admin_psychologue_show_legacy', methods: ['GET'])]
    public function adminPsychologueShow(Psychologue $psychologue): RedirectResponse
    {
        return $this->redirectToRoute('admin_psychologue_show', ['id' => $psychologue->getIdPsychologue()]);
    }

    #[Route('/admin/psychologue/{id}/edit', name: 'admin_psychologue_edit_legacy', methods: ['GET'])]
    public function adminPsychologueEdit(Psychologue $psychologue): RedirectResponse
    {
        return $this->redirectToRoute('admin_psychologue_edit', ['id' => $psychologue->getIdPsychologue()]);
    }
}
