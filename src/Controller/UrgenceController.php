<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Repository\AlerteRepository;
use App\Repository\UserRepository;
use App\Service\BrevoSmsService;
use App\Service\EmailService;
use App\Service\FirebaseNotificationService;
use App\Service\TelegramService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class UrgenceController extends AbstractController
{
    #[Route('/urgence', name: 'app_urgence_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('urgence/index.html.twig');
    }

    #[Route('/api/urgence/sos', name: 'api_urgence_sos', methods: ['POST'])]
    public function sos(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        FirebaseNotificationService $firebaseNotificationService,
        TelegramService $telegramService,
        BrevoSmsService $brevoSmsService,
        EmailService $emailService,
        Environment $twig
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $medecin = isset($data['medecinId']) ? $userRepository->find((int) $data['medecinId']) : $userRepository->findAnyMedecin();
        $patient = $userRepository->findAnyPatient();

        if (!$medecin || !$patient) {
            return $this->json(['success' => false, 'error' => 'Medecin ou patient introuvable.'], 400);
        }

        $distance = (float) ($data['distance'] ?? 0);
        $patientLat = (float) ($data['patientLat'] ?? ($patient->getLatitude() ?? 36.8065));
        $patientLon = (float) ($data['patientLon'] ?? ($patient->getLongitude() ?? 10.1815));

        $alerte = new Alerte();
        $alerte->setMedecin($medecin);
        $alerte->setPatient($patient);
        $alerte->setDistance(number_format($distance, 2, '.', ''));
        $alerte->setLatitude($patientLat);
        $alerte->setLongitude($patientLon);
        $alerte->setPatientNom($patient->getFullName());
        $alerte->setMedecinNom($medecin->getFullName());
        $alerte->setStatut('envoyee');

        $entityManager->persist($alerte);
        $entityManager->flush();

        $statuses = [
            'firebase' => false,
            'telegram' => false,
            'sms' => false,
            'email' => false,
        ];

        if ($medecin->getFcmToken()) {
            $statuses['firebase'] = $firebaseNotificationService->envoyerUrgence($medecin->getFcmToken(), $patient->getFullName(), $distance);
        }

        $statuses['telegram'] = $telegramService->sendUrgence(
            $patient->getFullName(),
            $distance,
            sprintf('https://www.google.com/maps?q=%s,%s', $patientLat, $patientLon),
            $medecin->getTelegramChatId()
        );

        if ($medecin->getPhone()) {
            $smsResult = $brevoSmsService->sendSms($medecin->getPhone(), sprintf('GrowMind SOS: %s a %.2f km.', $patient->getFullName(), $distance));
            $statuses['sms'] = (bool) ($smsResult['success'] ?? false);
        }

        $emailTarget = $medecin->getEmail() ?: ($_ENV['MAILER_NOTIFY_EMAIL'] ?? null);
        if ($emailTarget) {
            $statuses['email'] = $emailService->sendCustomEmail(
                $emailTarget,
                'URGENCE - Patient en detresse',
                $twig->render('email/urgence.html.twig', [
                    'patient' => $patient,
                    'medecin' => $medecin,
                    'distance' => number_format($distance, 2, '.', ''),
                    'latitude' => $patientLat,
                    'longitude' => $patientLon,
                ])
            );
        }

        return $this->json([
            'success' => true,
            'id' => $alerte->getId(),
            'statuses' => $statuses,
            'emailTarget' => $emailTarget,
        ]);
    }

    #[Route('/api/urgence/historique', name: 'api_urgence_historique', methods: ['GET'])]
    public function historique(AlerteRepository $alerteRepository): JsonResponse
    {
        $data = array_map(static function (Alerte $alerte): array {
            return [
                'id' => $alerte->getId(),
                'medecinNom' => $alerte->getMedecinNom(),
                'patientNom' => $alerte->getPatientNom(),
                'distance' => $alerte->getDistance(),
                'latitude' => $alerte->getLatitude(),
                'longitude' => $alerte->getLongitude(),
                'createdAt' => $alerte->getCreatedAt()?->format('d/m/Y H:i'),
                'statut' => $alerte->getStatut(),
            ];
        }, $alerteRepository->findBy([], ['createdAt' => 'DESC']));

        return $this->json($data);
    }

    #[Route('/api/urgence/supprimer/{id}', name: 'api_urgence_supprimer', methods: ['DELETE'])]
    public function supprimer(Alerte $alerte, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($alerte);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}
