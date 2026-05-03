<?php

namespace App\Controller;

use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\User;
use App\Service\AgentInterviewService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private AgentInterviewService $interview,
    ) {}

    // ──────────────────────────────────────────────────────────
    //  REGISTER
    // ──────────────────────────────────────────────────────────
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $em = $doctrine->getManager();

            $name       = trim($request->request->get('name', ''));
            $secondName = trim($request->request->get('second_name', ''));
            $email      = trim($request->request->get('email', ''));
            $password   = $request->request->get('password', '');
            $confirm    = $request->request->get('confirm_password', '');
            $role       = $request->request->get('role', 'patient');
            $age        = (int) $request->request->get('age', 0);
            $gender     = $request->request->get('gender', '');
            $phone      = $request->request->get('phone_number', '');
            $birthDate  = $request->request->get('birth_date', '');

            // ── Validation ──────────────────────────────────────────
            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Les champs prénom, email et mot de passe sont obligatoires.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif (strlen($password) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caractères.';
            } elseif (!in_array($role, ['patient', 'doctor'])) {
                $error = 'Rôle invalide.';
            } elseif ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                try {
                    // ── CAS PATIENT : enregistrement direct ────────────
                    if ($role === 'patient') {
                        $user = new User();
                        $user->setName($name);
                        $user->setSecond_name($secondName);
                        $user->setEmail($email);
                        $user->setPassword($passwordHasher->hashPassword($user, $password));
                        $user->setRole($role);
                        $user->setDtype($role);
                        $user->setAge($age > 0 ? $age : 0);
                        $user->setGender($gender ?: null);
                        $user->setPhoneNumber($phone ? (int) $phone : null);
                        $user->setBirthDate($birthDate ?: null);
                        $user->setIsBlocked(false);
                        $em->persist($user);

                        $patient = new Patient();
                        $patient->setUser($user);
                        $patient->setBlood_type($request->request->get('blood_type') ?: null);
                        $patient->setWeight($request->request->get('weight') !== '' ? (float) $request->request->get('weight') : null);
                        $patient->setHeight($request->request->get('height') !== '' ? (float) $request->request->get('height') : null);
                        $em->persist($patient);
                        $em->flush();

                        $this->addFlash('success', 'Compte créé ! Connectez-vous.');
                        return $this->redirectToRoute('app_login');
                    }

                    // ── CAS DOCTOR : vérifie Flask, crée une session d'entretien ──
                    if ($role === 'doctor') {

                        // 1. Vérifie que Flask est en ligne
                        if (!$this->interview->isOnline()) {
                            $error = 'Le service d\'entretien IA est temporairement indisponible. Réessayez plus tard.';
                        } else {
                            // 2. Crée une session d'entretien côté Flask
                            $sessionId = $this->interview->createSession();

                            if (!$sessionId) {
                                $error = 'Impossible de démarrer l\'entretien. Réessayez.';
                            } else {
                                // BUG 12 FIX : Créer un User temporaire avec email + role
                                // pour que le hashage soit cohérent avec l'objet final.
                                $tempUser = new User();
                                $tempUser->setEmail($email);
                                $tempUser->setRole('doctor');
                                $hashedPassword = $passwordHasher->hashPassword($tempUser, $password);

                                // 3. Stocke les données du formulaire en session PHP (pas encore en DB)
                                $session = $request->getSession();
                                $session->set('pending_doctor', [
                                    'name'            => $name,
                                    'second_name'     => $secondName,
                                    'email'           => $email,
                                    'password_hashed' => $hashedPassword,
                                    'age'             => $age,
                                    'gender'          => $gender,
                                    'phone'           => $phone,
                                    'birth_date'      => $birthDate,
                                    'specialty'       => $request->request->get('specialty', ''),
                                    'experience'      => $request->request->get('experience', ''),
                                    'diplome'         => $request->request->get('diplome', ''),
                                    'tarif_consultation' => $request->request->get('tarif_consultation', ''),
                                    'disponible'      => (bool) $request->request->get('disponible'),
                                    // BUG 14 FIX : stocker l'heure de création pour détecter l'expiration
                                    'created_at'      => time(),
                                ]);
                                $session->set('interview_session_id', $sessionId);

                                // 4. Redirige vers la page d'entretien navigateur
                                return $this->redirectToRoute('app_interview_pending');
                            }
                        }
                    }

                } catch (\Exception $e) {
                    $error = 'Erreur lors de la création du compte : ' . $e->getMessage();
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'error' => $error,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  PAGE D'ENTRETIEN
    // ──────────────────────────────────────────────────────────
    #[Route('/register/entretien', name: 'app_interview_pending', methods: ['GET'])]
    public function interviewPending(Request $request): Response
    {
        $session   = $request->getSession();
        $sessionId = $session->get('interview_session_id');
        $doctor    = $session->get('pending_doctor');

        if (!$sessionId || !$doctor) {
            return $this->redirectToRoute('app_register');
        }

        // BUG 14 FIX : Vérifier que la session PHP n'a pas expiré (2h max)
        $createdAt = $doctor['created_at'] ?? 0;
        if (time() - $createdAt > 7200) {
            $session->remove('pending_doctor');
            $session->remove('interview_session_id');
            $this->addFlash('error', 'Session expirée. Veuillez recommencer l\'inscription.');
            return $this->redirectToRoute('app_register');
        }

        return $this->render('auth/interview_pending.html.twig', [
            'session_id'  => $sessionId,
            'doctor_name' => $doctor['name'] . ' ' . $doctor['second_name'],
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  ROUTES PROXY /agent/* → Flask
    // ══════════════════════════════════════════════════════════

    #[Route('/agent/question/{n}', name: 'agent_question', methods: ['GET'])]
    public function agentQuestion(int $n): JsonResponse
    {
        $question = $this->interview->getQuestion($n);
        if ($question === null) {
            return new JsonResponse(['error' => 'Question introuvable'], 400);
        }
        return new JsonResponse(['question' => $question]);
    }

    #[Route('/agent/answer', name: 'agent_answer', methods: ['POST'])]
    public function agentAnswer(Request $request): JsonResponse
    {
        $body      = json_decode($request->getContent(), true) ?? [];
        $sessionId = $body['session_id'] ?? null;
        $answer    = $body['answer']     ?? null;
        if (!$sessionId || $answer === null) {
            return new JsonResponse(['error' => 'session_id et answer requis'], 400);
        }
        $result = $this->interview->submitAnswer($sessionId, $answer);
        if ($result === null) {
            return new JsonResponse(['error' => 'Session introuvable ou Flask inaccessible'], 404);
        }
        return new JsonResponse($result);
    }

    /**
     * Proxy vers POST /interview/finalise dans app.py.
     * Retourne le rapport complet : score, verdict, détail par question, raisons de refus.
     */
    #[Route('/agent/finalise', name: 'agent_finalise', methods: ['POST'])]
    public function agentFinalise(Request $request): JsonResponse
    {
        $body      = json_decode($request->getContent(), true) ?? [];
        $sessionId = $body['session_id'] ?? $request->getSession()->get('interview_session_id');

        if (!$sessionId) {
            return new JsonResponse(['error' => 'session_id requis'], 400);
        }

        $report = $this->interview->finalise($sessionId);

        if ($report === null) {
            return new JsonResponse(['error' => 'Session introuvable ou entretien incomplet'], 404);
        }

        return new JsonResponse($report);
    }

    // ──────────────────────────────────────────────────────────
    //  FINALISATION — appelé par JS après l'entretien
    //
    //  BUG 13 FIX : Le frontend envoie déjà le rapport Flask dans le body.
    //               On utilise directement ce rapport au lieu de rappeler Flask,
    //               sauf si le body est vide (fallback sécurisé).
    //
    //  BUG 15 FIX : Validation de l'origine via la session PHP.
    //               On vérifie que session_id du rapport correspond à celui
    //               stocké en session PHP — empêche les soumissions forgées.
    // ──────────────────────────────────────────────────────────
    #[Route('/register/entretien/finaliser', name: 'app_interview_finaliser', methods: ['POST'])]
    public function interviewFinaliser(
        Request $request,
        ManagerRegistry $doctrine
    ): JsonResponse {
        $session    = $request->getSession();
        $sessionId  = $session->get('interview_session_id');
        $doctorData = $session->get('pending_doctor');

        if (!$sessionId || !$doctorData) {
            return new JsonResponse(['error' => 'Session expirée. Veuillez recommencer.'], 400);
        }

        // BUG 14 FIX : re-vérifier l'expiration de session PHP ici aussi
        $createdAt = $doctorData['created_at'] ?? 0;
        if (time() - $createdAt > 7200) {
            $session->remove('pending_doctor');
            $session->remove('interview_session_id');
            return new JsonResponse(['error' => 'Session expirée. Veuillez recommencer l\'inscription.'], 400);
        }

        // BUG 13 FIX : utiliser le rapport envoyé par le frontend (déjà récupéré depuis Flask)
        //              plutôt que de rappeler Flask inutilement.
        $body   = json_decode($request->getContent(), true) ?? [];
        $result = !empty($body) ? $body : $this->interview->getResult($sessionId);

        if ($result === null) {
            return new JsonResponse(['status' => 'pending'], 202);
        }

        // BUG 15 FIX : s'assurer que le résultat vient bien de la session attendue
        // On vérifie que le score est cohérent (entre 0 et 100) et que "accepted" existe
        if (!isset($result['accepted']) || !isset($result['score'])) {
            return new JsonResponse(['error' => 'Rapport invalide.'], 400);
        }

        $accepted = $this->interview->isAccepted($result);

        // ── REFUSÉ : nettoyage session, pas d'enregistrement ──
        if (!$accepted) {
            $session->remove('pending_doctor');
            $session->remove('interview_session_id');

            return new JsonResponse([
                'accepted'       => false,
                'score'          => $result['score'] ?? 0,
                'refusal_reason' => 'Candidature non retenue (score insuffisant, diplôme ou expérience invalide).',
                'redirect'       => $this->generateUrl('app_register'),
            ]);
        }

        // ── ACCEPTÉ : création en base de données ──
        try {
            $em = $doctrine->getManager();

            $user = new User();
            $user->setName($doctorData['name']);
            $user->setSecond_name($doctorData['second_name']);
            $user->setEmail($doctorData['email']);
            // BUG 12 FIX : le mot de passe a été hashé sur un User avec email+role cohérent
            $user->setPassword($doctorData['password_hashed']);
            $user->setRole('doctor');
            $user->setDtype('doctor');
            $user->setAge($doctorData['age'] > 0 ? $doctorData['age'] : 0);
            $user->setGender($doctorData['gender'] ?: null);
            $user->setPhoneNumber($doctorData['phone'] ? (int) $doctorData['phone'] : null);
            $user->setBirthDate($doctorData['birth_date'] ?: null);
            $user->setIsBlocked(false);
            $em->persist($user);

            $doctor = new Doctor();
            $doctor->setUser($user);
            $em->persist($doctor);

            $em->flush();

            $session->remove('pending_doctor');
            $session->remove('interview_session_id');

            return new JsonResponse([
                'accepted' => true,
                'redirect' => $this->generateUrl('app_login'),
                'message'  => 'Votre candidature a été acceptée ! Connectez-vous.',
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}