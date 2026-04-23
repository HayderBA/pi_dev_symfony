<?php

namespace App\Controller;

use App\Form\HealthApiToolsType;
use App\Service\BmiApiClient;
use App\Service\CalorieApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WellnessApiController extends AbstractController
{
    #[Route('/bien-etre/api-services', name: 'app_wellness_api_services', methods: ['GET', 'POST'])]
    public function index(Request $request, BmiApiClient $bmiApiClient, CalorieApiClient $calorieApiClient): Response
    {
        $form = $this->createForm(HealthApiToolsType::class);
        $form->handleRequest($request);

        $imcResult = null;
        $caloriesResult = null;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{poids: float|int|string, taille: float|int|string, nutritionQuery: string} $data */
            $data = $form->getData();

            $weight = (float) $data['poids'];
            $heightMeters = (float) $data['taille'];

            $imcResult = $bmiApiClient->calculate($weight, $heightMeters);
            $caloriesResult = $calorieApiClient->analyzeNutritionQuery($data['nutritionQuery']);
        }

        return $this->render('wellness/api_services/index.html.twig', [
            'form' => $form,
            'imc_result' => $imcResult,
            'calories_result' => $caloriesResult,
        ]);
    }
}
