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

final class MentalHealthController extends AbstractController
{
    #[Route('/mental-health', name: 'mental_health_page', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || !$user->isPatient()) {
            return $this->redirectToRoute('app_login');
        }

        $lastResult = $entityManager->getConnection()->fetchAssociative(
            'SELECT * FROM mental_health_check WHERE patient_id = ? ORDER BY checked_at DESC LIMIT 1',
            [$user->getId()]
        );

        if ($lastResult) {
            $lastChecked = new \DateTime((string) $lastResult['checked_at']);
            $now = new \DateTime();
            $diff = $now->diff($lastChecked);
            $hoursDiff = ($diff->days * 24) + $diff->h;

            if ($hoursDiff >= 24) {
                $lastResult = null;
            }
        }

        return $this->render('mental_health/index.html.twig', [
            'user' => $user,
            'lastResult' => $lastResult,
        ]);
    }

    #[Route('/mental-health/generate', name: 'mental_health_generate', methods: ['POST'])]
    public function generateGames(HttpClientInterface $client): JsonResponse
    {
        $groqKey = trim((string) $this->getParameter('app.groq_api_key'));
        if ('' === $groqKey) {
            return $this->json(['games' => $this->getFallbackGames()]);
        }

        $prompt = <<<'PROMPT'
Tu es un psychologue creatif et bienveillant. Cree 5 mini-jeux originaux en francais pour evaluer l'etat emotionnel d'un patient.

TYPES DISPONIBLES (choisis parmi ces 5 uniquement) :
1. emoji_choice
2. slider
3. color_pick
4. word_association
5. scenario

Contraintes:
- 5 jeux avec types varies
- options sous forme de tableau
- weights obligatoire sauf pour slider
- reponds uniquement en JSON valide

FORMAT:
{"games":[{"id":1,"type":"emoji_choice","question":"...","options":["..."],"weights":{"...":1}}]}
PROMPT;

        try {
            $response = $client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $groqKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.1-8b-instant',
                    'temperature' => 0.9,
                    'max_tokens' => 1600,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ],
            ]);

            $data = $response->toArray(false);
            $content = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
            $content = preg_replace('/```json|```/', '', $content);
            $decoded = json_decode((string) $content, true);

            if (!is_array($decoded) || !isset($decoded['games']) || !is_array($decoded['games'])) {
                return $this->json(['games' => $this->getFallbackGames()]);
            }

            return $this->json(['games' => $decoded['games']]);
        } catch (\Throwable) {
            return $this->json(['games' => $this->getFallbackGames()]);
        }
    }

    #[Route('/mental-health/analyze', name: 'mental_health_analyze', methods: ['POST'])]
    public function analyze(
        Request $request,
        HttpClientInterface $client,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || !$user->isPatient()) {
            return $this->json(['error' => 'Utilisateur non autorise.'], 403);
        }

        $data = json_decode((string) $request->getContent(), true);
        $answers = $data['answers'] ?? [];
        $games = $data['games'] ?? [];

        $result = $this->analyzeWithGroq($client, $answers, $games);
        if (null === $result) {
            $result = $this->analyzeLocally($answers, $games);
        }

        $connection = $entityManager->getConnection();
        $connection->executeStatement('DELETE FROM mental_health_check WHERE patient_id = ?', [$user->getId()]);
        $connection->insert('mental_health_check', [
            'patient_id' => $user->getId(),
            'checked_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'games_data' => json_encode($answers, JSON_THROW_ON_ERROR),
            'ai_result' => $result['result'],
            'ai_advice' => $result['advice'],
            'ai_score' => $result['score'],
        ]);

        return $this->json($result);
    }

    private function analyzeWithGroq(HttpClientInterface $client, array $answers, array $games): ?array
    {
        $groqKey = trim((string) $this->getParameter('app.groq_api_key'));
        if ('' === $groqKey) {
            return null;
        }

        $summary = [];
        foreach ($answers as $index => $answer) {
            $game = $games[$index] ?? [];
            $summary[] = sprintf(
                "Jeu %d - %s\nReponse: %s",
                $index + 1,
                (string) ($game['question'] ?? 'Question'),
                (string) ($answer['value'] ?? '')
            );
        }

        $prompt = "Tu es un psychologue bienveillant. Analyse ces reponses d'un patient.\n\n"
            . implode("\n\n", $summary)
            . "\n\nReponds uniquement en JSON valide : "
            . '{"result":"ok","score":75,"emoji":"🙂","label":"Vous allez bien","advice":"...","alert":false}';

        try {
            $response = $client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $groqKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.1-8b-instant',
                    'temperature' => 0.7,
                    'max_tokens' => 800,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ],
            ]);

            $data = $response->toArray(false);
            $content = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
            $content = preg_replace('/```json|```/', '', $content);
            $decoded = json_decode((string) $content, true);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function analyzeLocally(array $answers, array $games): array
    {
        $score = 55;

        foreach ($answers as $index => $answer) {
            $game = $games[$index] ?? [];
            $value = $answer['value'] ?? null;

            if ('slider' === ($game['type'] ?? null)) {
                $sliderValue = is_numeric($value) ? (int) $value : 50;
                $score += (int) round(($sliderValue - 50) / 8);
                continue;
            }

            $weights = $game['weights'] ?? [];
            if (is_array($weights) && null !== $value && isset($weights[$value])) {
                $score += (int) $weights[$value] * 8;
            }
        }

        $score = max(0, min(100, $score));

        if ($score < 30) {
            return [
                'result' => 'depression',
                'score' => $score,
                'emoji' => '😔',
                'label' => 'Vous semblez traverse une phase delicate',
                'advice' => 'Parlez a un professionnel, ralentissez le rythme et sollicitez un proche de confiance des aujourd hui.',
                'alert' => true,
            ];
        }

        if ($score < 50) {
            return [
                'result' => 'anxiete',
                'score' => $score,
                'emoji' => '😰',
                'label' => 'Un niveau d anxiete est detecte',
                'advice' => 'Essayez une respiration lente, une marche courte et evitez de rester seule avec la charge mentale.',
                'alert' => false,
            ];
        }

        if ($score < 70) {
            return [
                'result' => 'stress',
                'score' => $score,
                'emoji' => '😕',
                'label' => 'Vous paraissez sous pression',
                'advice' => 'Faites une pause, hydratez-vous et choisissez une seule priorite realiste pour aujourd hui.',
                'alert' => false,
            ];
        }

        return [
            'result' => 'ok',
            'score' => $score,
            'emoji' => '😊',
            'label' => 'Vous allez plutot bien',
            'advice' => 'Gardez votre rythme actuel, continuez vos habitudes positives et accordez-vous un moment de recuperation.',
            'alert' => false,
        ];
    }

    private function getFallbackGames(): array
    {
        return [
            [
                'id' => 1,
                'type' => 'emoji_choice',
                'question' => 'Comment vous sentez-vous en ce moment ?',
                'options' => ['😄', '🙂', '😐', '😕', '😢'],
                'weights' => ['😄' => 2, '🙂' => 1, '😐' => 0, '😕' => -1, '😢' => -2],
            ],
            [
                'id' => 2,
                'type' => 'slider',
                'question' => 'Quel est votre niveau de stress aujourd hui ?',
                'options' => ['Tres calme', 'Tres stresse'],
            ],
            [
                'id' => 3,
                'type' => 'color_pick',
                'question' => 'Quelle couleur ressemble le plus a votre journee ?',
                'options' => ['#FFD700', '#87CEEB', '#90EE90', '#FF6B6B', '#9B59B6'],
                'weights' => ['#FFD700' => 2, '#87CEEB' => 1, '#90EE90' => 2, '#FF6B6B' => -1, '#9B59B6' => 0],
            ],
            [
                'id' => 4,
                'type' => 'word_association',
                'question' => 'Quel mot vous correspond le mieux maintenant ?',
                'options' => ['Serein', 'Fatigue', 'Anxieux', 'Motive', 'Triste'],
                'weights' => ['Serein' => 2, 'Fatigue' => -1, 'Anxieux' => -2, 'Motive' => 2, 'Triste' => -2],
            ],
            [
                'id' => 5,
                'type' => 'scenario',
                'question' => 'Face a un probleme inattendu, vous...',
                'options' => ['Je cherche une solution', 'Je demande de l aide', 'Je panique', 'Je l ignore', 'Je me decourage'],
                'weights' => ['Je cherche une solution' => 2, 'Je demande de l aide' => 1, 'Je panique' => -2, 'Je l ignore' => -1, 'Je me decourage' => -2],
            ],
        ];
    }
}
