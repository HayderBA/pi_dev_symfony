<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MentalHealthController extends AbstractController
{
    /**
     * 🧠 Page principale du mini-jeu mental health
     * Vérifie si le patient a déjà fait son check dans les 24h
     */
    #[Route('/mental-health', name: 'mental_health_page')]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier dernière session < 24h
        $conn = $em->getConnection();
        $row = $conn->fetchAssociative(
            'SELECT * FROM mental_health_check WHERE patient_id = ? ORDER BY checked_at DESC LIMIT 1',
            [$user->getId()]
        );

        $lastResult = null;

        if ($row) {
            $lastChecked = new \DateTime($row['checked_at']);
            $now = new \DateTime();
            $diff = $now->diff($lastChecked);
            $hoursDiff = ($diff->days * 24) + $diff->h;

            if ($hoursDiff < 24) {
                // Déjà fait aujourd'hui → afficher résultat précédent
                $lastResult = $row;
            }
        }

        return $this->render('mental_health/index.html.twig', [
            'user'       => $user,
            'lastResult' => $lastResult,
        ]);
    }

    /**
     * 🎮 Générer les mini-jeux via Groq AI
     */
    #[Route('/mental-health/generate', name: 'mental_health_generate', methods: ['POST'])]
    public function generateGames(HttpClientInterface $client): JsonResponse
    {
        // Debug : vérifier que la clé est bien chargée
        $key = $this->getParameter('app.groq_api_key');
        if (!$key || str_starts_with((string)$key, '%')) {
            return new JsonResponse(['error' => 'Clé API Groq non configurée dans services.yaml / .env'], 500);
        }

        $prompt = <<<'PROMPT'
Tu es un psychologue créatif et bienveillant. Crée 5 mini-jeux originaux en français pour évaluer l'état émotionnel d'un patient.

TYPES DISPONIBLES (choisis parmi ces 5 uniquement) :
1. emoji_choice → le patient choisit un emoji
2. slider → le patient glisse un curseur entre deux extrêmes
3. color_pick → le patient choisit une couleur (format #hex)
4. word_association → le patient choisit un mot
5. scenario → le patient réagit à une situation de vie

CONTRAINTES :
- 5 jeux avec des types VARIÉS (pas le même type deux fois de suite)
- Questions originales, jamais vues, adaptées à la santé mentale
- Ton chaleureux, jamais médical ni clinique
- options : tableau de strings (emojis, mots, couleurs hex, ou phrases)
- weights : obligatoire pour emoji_choice, color_pick, word_association, scenario (valeurs entre -2 et 2)
- weights : ABSENT pour slider
- Réponds UNIQUEMENT en JSON valide, sans markdown, sans texte avant ou après

FORMAT JSON STRICT :
{"games":[{"id":1,"type":"TYPE","question":"...","options":[...],"weights":{...}}]}

Pour slider uniquement : {"id":2,"type":"slider","question":"...","options":["label_gauche","label_droite"]}
PROMPT;

        try {
            $response = $client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $key,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'llama-3.1-8b-instant',
                    'temperature' => 0.9,
                    'max_tokens'  => 2000,
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                ],
            ]);

            $data    = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $content = preg_replace('/```json|```/', '', $content);
            $content = trim($content);
            $games   = json_decode($content, true);

            if (!$games || !isset($games['games'])) {
                return new JsonResponse(['error' => 'Réponse IA invalide', 'raw' => $content], 500);
            }

            return new JsonResponse($games);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🧠 Analyser les réponses et générer le conseil IA
     */
    #[Route('/mental-health/analyze', name: 'mental_health_analyze', methods: ['POST'])]
    public function analyze(Request $request, HttpClientInterface $client, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user    = $this->getUser();
        $data    = json_decode($request->getContent(), true);
        $answers = $data['answers'] ?? [];
        $games   = $data['games']   ?? [];

        $summary = "";
        foreach ($answers as $i => $answer) {
            $game     = $games[$i] ?? [];
            $summary .= "Jeu " . ($i + 1) . " - " . ($game['question'] ?? '') . "\n";
            $summary .= "Réponse : " . $answer['value'] . "\n\n";
        }

        $key = $this->getParameter('app.groq_api_key');

        $prompt = <<<PROMPT
Tu es un psychologue bienveillant. Analyse ces réponses d'un patient.

RÉPONSES :
$summary

Réponds UNIQUEMENT en JSON valide sans markdown :
{"result":"ok","score":75,"emoji":"😊","label":"Vous allez bien","advice":"Message motivant ici...","alert":false}

result = ok, stress, anxiete, ou depression
score = 0 à 100
alert = true si score < 30
PROMPT;

        try {
            $response = $client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $key,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'llama-3.1-8b-instant',
                    'temperature' => 0.7,
                    'max_tokens'  => 800,
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                ],
            ]);

            $data    = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $content = preg_replace('/```json|```/', '', $content);
            $content = trim($content);
            $result  = json_decode($content, true);

            if (!$result) {
                return new JsonResponse(['error' => 'Analyse IA invalide', 'raw' => $content], 500);
            }

            $conn = $em->getConnection();
            $conn->executeStatement('DELETE FROM mental_health_check WHERE patient_id = ?', [$user->getId()]);
            $conn->executeStatement(
                'INSERT INTO mental_health_check (patient_id, checked_at, games_data, ai_result, ai_advice, ai_score) VALUES (?, NOW(), ?, ?, ?, ?)',
                [$user->getId(), json_encode($answers), $result['result'], $result['advice'], $result['score']]
            );

            return new JsonResponse($result);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}