<?php

namespace App\Controller;

use App\Form\HealthApiToolsType;
use App\Service\CaloriesApiService;
use App\Service\IMCApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApiServicesController extends AbstractController
{
    #[Route('/sante-bien-etre/api-services', name: 'app_api_services', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        IMCApiService $imcApiService,
        CaloriesApiService $caloriesApiService,
    ): Response {
        $form = $this->createForm(HealthApiToolsType::class);
        $form->handleRequest($request);

        $imcResult = null;
        $caloriesResult = null;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{poids: float|int|string, taille: float|int|string, nutritionQuery: string} $data */
            $data = $form->getData();

            $imcResult = $imcApiService->calculate(
                (float) $data['poids'],
                (float) $data['taille']
            );

            $caloriesResult = $caloriesApiService->fetchNutritionData($data['nutritionQuery']);
        }

        return $this->render('api_services/index.html.twig', [
            'form' => $form,
            'imc_result' => $imcResult,
            'calories_result' => $caloriesResult,
        ]);
    }
}
