<?php

namespace App\Controller;

use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FaceController extends AbstractController
{
    /**
     * 🟢 Page webcam Face ID
     */
    #[Route('/face', name: 'face_page')]
    public function facePage(AdminRepository $adminRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $admin = $adminRepository->findOneBy(['user' => $user]);
        $hasFace = $admin && $admin->getFaceImage();

        return $this->render('reconnaissanceFacial/face.html.twig', [
            'has_face' => $hasFace
        ]);
    }

    /**
     * 📸 Enregistrer la photo de référence de l'admin connecté
     */
    #[Route('/face-upload', name: 'face_upload', methods: ['POST'])]
    public function faceUpload(
        Request $request,
        AdminRepository $adminRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            if (!$user) {
                return new JsonResponse(['error' => 'Non connecté'], 401);
            }

            $admin = $adminRepository->findOneBy(['user' => $user]);

            if (!$admin) {
                return new JsonResponse(['error' => 'Admin introuvable'], 404);
            }

            $data = json_decode($request->getContent(), true);
            $imageData = $data['image'] ?? null;

            if (!$imageData) {
                return new JsonResponse(['error' => 'Image manquante'], 400);
            }

            $base64 = explode(',', $imageData)[1] ?? null;

            if (!$base64) {
                return new JsonResponse(['error' => 'Image invalide'], 400);
            }

            // Créer le dossier faces/ si besoin
            $dir = $this->getParameter('kernel.project_dir') . '/public/faces';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            // Sauvegarder la photo
            $filename = 'faces/admin_' . $user->getId() . '.jpg';
            $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
            file_put_contents($fullPath, base64_decode($base64));

            // Mettre à jour l'entité Admin
            $admin->setFaceImage($filename);
            $em->flush();

            return new JsonResponse(['success' => true]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error'   => 'Erreur serveur',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 Comparer le visage webcam avec la photo de référence de l'admin
     */
    #[Route('/face-test', name: 'face_test', methods: ['POST'])]
    public function faceTest(
        Request $request,
        HttpClientInterface $client,
        AdminRepository $adminRepository
    ): JsonResponse {
        try {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            if (!$user) {
                return new JsonResponse(['error' => 'Non connecté'], 401);
            }

            // Récupérer l'admin connecté
            $admin = $adminRepository->findOneBy(['user' => $user]);

            if (!$admin) {
                return new JsonResponse(['error' => 'Admin introuvable'], 404);
            }

            if (!$admin->getFaceImage()) {
                return new JsonResponse(['error' => 'Aucune photo de référence enregistrée. Cliquez sur "Enregistrer mon visage" d\'abord.'], 404);
            }

            // Image capturée par la webcam
            $data = json_decode($request->getContent(), true);

            if (!isset($data['image'])) {
                return new JsonResponse(['error' => 'Image manquante'], 400);
            }

            $imageParts = explode(',', $data['image']);
            $image1 = $imageParts[1] ?? null;

            if (!$image1) {
                return new JsonResponse(['error' => 'Image invalide'], 400);
            }

            // Image de référence de l'admin
            $path = $this->getParameter('kernel.project_dir') . '/public/' . $admin->getFaceImage();

            if (!file_exists($path)) {
                return new JsonResponse(['error' => 'Fichier photo de référence introuvable sur le serveur'], 500);
            }

            $image2 = base64_encode(file_get_contents($path));

            // Appel Face++ API
            $response = $client->request(
                'POST',
                'https://api-us.faceplusplus.com/facepp/v3/compare',
                [
                    'body' => [
                        'api_key'        => $this->getParameter('app.face_api_key'),
                        'api_secret'     => $this->getParameter('app.face_api_secret'),
                        'image_base64_1' => $image1,
                        'image_base64_2' => $image2,
                    ]
                ]
            );

            $result = $response->toArray();
            $confidence = $result['confidence'] ?? 0;

            return new JsonResponse([
                'confidence' => $confidence,
                'success'    => $confidence > 80
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error'   => 'Erreur serveur',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}