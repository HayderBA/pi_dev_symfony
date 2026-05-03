<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service Symfony pour communiquer avec l'agent IA GrowMind (app.py + Flask).
 *
 * Installation: composer require symfony/http-client
 *
 * L'agent tourne sur http://localhost:5001
 * Routes disponibles dans app.py :
 *   POST /interview/create        → crée une session, retourne {"session_id": "..."}
 *   GET  /interview/question/{n}  → retourne {"question": "..."}
 *   POST /interview/answer        → soumet une réponse, retourne le résultat d'analyse
 *   GET  /interview/result/{sid}  → résultat final {"score", "accepted", "answers", ...}
 */
class AgentInterviewService
{
    private string $apiUrl;

    public function __construct(
        private HttpClientInterface $client,
        string $agentApiUrl = 'http://localhost:5001'
    ) {
        $this->apiUrl = rtrim($agentApiUrl, '/');
    }

    // ──────────────────────────────────────────────────────────
    //  HEALTH CHECK
    //  app.py n'expose pas de route /health — on sonde /interview/create
    //  avec un timeout court pour détecter si Flask est joignable.
    // ──────────────────────────────────────────────────────────

    /**
     * Vérifie que app.py tourne et que Flask répond.
     * Utilise POST /interview/create comme sonde (pas de route /health dans app.py).
     * Retourne true si Flask est joignable, false sinon.
     */
    public function isOnline(): bool
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl . '/interview/create', [
                'timeout' => 3,
                'json'    => [],
            ]);
            // Flask est en ligne si on reçoit un JSON avec session_id
            $data = $response->toArray(false);
            return isset($data['session_id']);
        } catch (\Exception) {
            return false;
        }
    }

    // ──────────────────────────────────────────────────────────
    //  SESSION
    // ──────────────────────────────────────────────────────────

    /**
     * Crée une nouvelle session d'entretien côté Flask.
     * Correspond à : POST /interview/create → {"session_id": "<uuid>"}
     *
     * @return string|null  L'identifiant de session, ou null en cas d'échec.
     */
    public function createSession(): ?string
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl . '/interview/create', [
                'timeout' => 5,
                'json'    => [],
            ]);
            $data = $response->toArray();
            return $data['session_id'] ?? null;
        } catch (\Exception) {
            return null;
        }
    }

    // ──────────────────────────────────────────────────────────
    //  QUESTIONS
    // ──────────────────────────────────────────────────────────

    /**
     * Récupère la question n° $index (0-based) depuis app.py.
     * Correspond à : GET /interview/question/{n} → {"question": "..."}
     *
     * @return string|null  Le texte de la question, ou null si hors-bornes / erreur.
     */
    public function getQuestion(int $index): ?string
    {
        try {
            $response = $this->client->request(
                'GET',
                $this->apiUrl . '/interview/question/' . $index,
                ['timeout' => 5]
            );

            if ($response->getStatusCode() === 400) {
                return null; // index hors-bornes
            }

            $data = $response->toArray();
            return $data['question'] ?? null;
        } catch (\Exception) {
            return null;
        }
    }

    // ──────────────────────────────────────────────────────────
    //  RÉPONSES
    // ──────────────────────────────────────────────────────────

    /**
     * Soumet la réponse du candidat pour la question courante.
     * Correspond à : POST /interview/answer
     *   body : {"session_id": "...", "answer": "..."}
     *
     * Retour possible selon la question :
     *   Q0 (spécialité)  → {"message": "Spécialité enregistrée",  "note": 20}
     *   Q1 (diplôme)     → {"message": "Diplôme enregistré",      "note": 20}
     *   Q2 (expérience)  → {"message": "Expérience enregistrée",  "note": 20}
     *   Q3+ (clinique)   → {"note": int, "verdict": str, "score": float, "matched_keywords": int}
     *
     * @return array|null  Tableau de résultat, ou null en cas d'erreur.
     */
    public function submitAnswer(string $sessionId, string $answer): ?array
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl . '/interview/answer', [
                'timeout' => 10,
                'json'    => [
                    'session_id' => $sessionId,
                    'answer'     => $answer,
                ],
            ]);

            if ($response->getStatusCode() === 404) {
                return null; // session introuvable
            }

            return $response->toArray();
        } catch (\Exception) {
            return null;
        }
    }

    // ──────────────────────────────────────────────────────────
    //  RÉSULTAT FINAL
    // ──────────────────────────────────────────────────────────

    /**
     * Récupère le résultat final de l'entretien.
     * Correspond à : GET /interview/result/{sid}
     *
     * Retour :
     * {
     *   "score":      int,        // sur 100
     *   "accepted":   bool,
     *   "specialite": string,
     *   "diplome":    string,
     *   "experience": int,
     *   "answers":    array
     * }
     *
     * Conditions d'acceptation dans app.py :
     *   - diplome ∈ {MD, MPSY}
     *   - experience >= 2
     *   - score >= 50
     *
     * @return array|null  Tableau de résultat, ou null si session introuvable / erreur.
     */
    public function getResult(string $sessionId): ?array
    {
        try {
            $response = $this->client->request(
                'GET',
                $this->apiUrl . '/interview/result/' . urlencode($sessionId),
                ['timeout' => 5]
            );

            if ($response->getStatusCode() === 404) {
                return null; // session introuvable
            }

            return $response->toArray();
        } catch (\Exception) {
            return null;
        }
    }

    // ──────────────────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────────────────

    /**
     * Retourne true si le résultat indique que le candidat a été accepté.
     * Raccourci pour éviter de répéter la vérification du champ "accepted".
     */
    public function isAccepted(array $result): bool
    {
        return ($result['accepted'] ?? false) === true;
    }

    // ──────────────────────────────────────────────────────────
    //  FINALISE
    // ──────────────────────────────────────────────────────────

    /**
     * Génère le rapport final de l'entretien.
     * Correspond à : POST /interview/finalise
     *   body : {"session_id": "..."}
     *
     * Retour :
     * {
     *   "status":          "accepted"|"refused",
     *   "accepted":        bool,
     *   "score":           int,        // sur 100
     *   "specialite":      string,
     *   "diplome":         string,
     *   "experience":      int,
     *   "refusal_reasons": string[],   // vide si accepté
     *   "details": [
     *     {"question", "answer", "note", "note_max", "verdict"},
     *     ...
     *   ],
     *   "criteria": {
     *     "diplome_ok":    bool,
     *     "experience_ok": bool,
     *     "score_ok":      bool
     *   }
     * }
     *
     * @return array|null  Rapport complet, ou null si session introuvable / entretien incomplet.
     */
    public function finalise(string $sessionId): ?array
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl . '/interview/finalise', [
                'timeout' => 10,
                'json'    => ['session_id' => $sessionId],
            ]);

            if (in_array($response->getStatusCode(), [404, 400])) {
                return null; // session introuvable ou entretien incomplet
            }

            return $response->toArray();
        } catch (\Exception) {
            return null;
        }
    }
}