<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FaceController extends AbstractController
{
    #[Route('/face', name: 'face_page')]
    public function facePage(): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user || !$user->isAdmin()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('reconnaissanceFacial/face.html.twig', [
            'has_face' => null !== $user->getFaceImage(),
        ]);
    }

    #[Route('/face-upload', name: 'face_upload', methods: ['POST'])]
    public function faceUpload(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User|null $user */
            $user = $this->getUser();

            if (!$user || !$user->isAdmin()) {
                return new JsonResponse(['error' => 'Non connecte'], 401);
            }

            $data = json_decode((string) $request->getContent(), true);
            $imageData = $data['image'] ?? null;

            if (!$imageData) {
                return new JsonResponse(['error' => 'Image manquante'], 400);
            }

            $base64 = explode(',', (string) $imageData)[1] ?? null;
            if (!$base64) {
                return new JsonResponse(['error' => 'Image invalide'], 400);
            }

            $dir = $this->getParameter('kernel.project_dir') . '/public/faces';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $filename = 'faces/admin_' . $user->getId() . '.jpg';
            $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
            file_put_contents($fullPath, base64_decode($base64));

            $user->setFaceImage($filename);
            $entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Throwable $exception) {
            return new JsonResponse([
                'error' => 'Erreur serveur',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    #[Route('/face-test', name: 'face_test', methods: ['POST'])]
    public function faceTest(Request $request, HttpClientInterface $client): JsonResponse
    {
        try {
            /** @var User|null $user */
            $user = $this->getUser();

            if (!$user || !$user->isAdmin()) {
                return new JsonResponse(['error' => 'Non connecte'], 401);
            }

            if (!$user->getFaceImage()) {
                return new JsonResponse(['error' => 'Aucune photo de reference enregistree. Cliquez sur "Enregistrer mon visage" d abord.'], 404);
            }

            $data = json_decode((string) $request->getContent(), true);
            if (!isset($data['image'])) {
                return new JsonResponse(['error' => 'Image manquante'], 400);
            }

            $imageParts = explode(',', (string) $data['image']);
            $image1 = $imageParts[1] ?? null;
            if (!$image1) {
                return new JsonResponse(['error' => 'Image invalide'], 400);
            }

            $path = $this->getParameter('kernel.project_dir') . '/public/' . $user->getFaceImage();
            if (!file_exists($path)) {
                return new JsonResponse(['error' => 'Fichier photo de reference introuvable sur le serveur'], 500);
            }

            $image2 = base64_encode((string) file_get_contents($path));

            $response = $client->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/compare', [
                'body' => [
                    'api_key' => $this->getParameter('app.face_api_key'),
                    'api_secret' => $this->getParameter('app.face_api_secret'),
                    'image_base64_1' => $image1,
                    'image_base64_2' => $image2,
                ],
            ]);

            $result = $response->toArray(false);
            $confidence = isset($result['confidence']) ? (float) $result['confidence'] : 0.0;

            return new JsonResponse([
                'confidence' => $confidence,
                'success' => $confidence > 80,
            ]);
        } catch (\Throwable $exception) {
            return new JsonResponse([
                'error' => 'Erreur serveur',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
