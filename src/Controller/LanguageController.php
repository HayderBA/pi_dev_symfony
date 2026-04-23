<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    #[Route('/language/{_locale}', name: 'app_language_switch', requirements: ['_locale' => 'fr|en'])]
    public function switchLanguage(string $_locale, Request $request): RedirectResponse
    {
        $request->getSession()->set('_locale', $_locale);

        $redirect = (string) $request->query->get('redirect', '');
        if ($redirect !== '' && str_starts_with($redirect, '/')) {
            return $this->redirect($redirect);
        }

        return $this->redirectToRoute('app_forum_index');
    }
}
