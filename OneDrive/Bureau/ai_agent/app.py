from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import uuid
import time
import threading

# ==============================
# CONFIG
# ==============================
app = Flask(__name__)
CORS(app)

# charger dataset
data = pd.read_csv("mental_health_full_dataset.csv")

# ==============================
# STOCKAGE SESSIONS
# BUG 8 FIX : On ajoute un timestamp de création pour pouvoir
#             nettoyer les sessions expirées (anti memory-leak).
#             Pour une vraie prod, utiliser Redis ou une DB.
# ==============================
sessions = {}
SESSION_TTL = 7200  # 2 heures en secondes

def cleanup_sessions():
    """BUG 11 FIX : Supprime les sessions expirées toutes les 30 min."""
    while True:
        time.sleep(1800)
        now = time.time()
        expired = [sid for sid, s in sessions.items()
                   if now - s.get("created_at", now) > SESSION_TTL]
        for sid in expired:
            del sessions[sid]
        if expired:
            app.logger.info(f"[cleanup] {len(expired)} session(s) supprimée(s)")

threading.Thread(target=cleanup_sessions, daemon=True).start()

# ==============================
# QUESTIONS ENTRETIEN
# ==============================
QUESTIONS = [
    "Donnez votre spécialité médicale",
    "Quel est votre diplôme ?",
    "Combien d'années d'expérience avez-vous ?",
    "Comment diagnostiquer une depression ?"
]

# ==============================
# EXTRACTION HELPERS
# ==============================
import re as _re

DIPLOME_MAP = {
    "MPSY": ["MPSY", "PSYCHIATRE", "PSYCHOLOGUE", "PSYCHOLOG"],
    "MD":   ["MD", "MEDECIN", "DOCTEUR", "DOCTOR", "MEDICINE", "MEDECINE"],
}

def extract_diplome(answer):
    upper = answer.upper()
    import unicodedata
    upper_norm = ''.join(
        c for c in unicodedata.normalize('NFD', upper)
        if unicodedata.category(c) != 'Mn'
    )
    for code, keywords in DIPLOME_MAP.items():
        for kw in keywords:
            if kw in upper_norm or kw in upper:
                return code
    return answer.strip().upper()

def extract_experience(answer):
    word_map = {
        # nombres simples
        "un": 1, "une": 1, "deux": 2, "trois": 3, "quatre": 4, "cinq": 5,
        "six": 6, "sept": 7, "huit": 8, "neuf": 9, "dix": 10,
        "onze": 11, "douze": 12, "quinze": 15, "vingt": 20, "trente": 30,
        # BUG 10 FIX : approximatifs courants
        "dizaine": 10, "vingtaine": 20, "quinzaine": 15, "douzaine": 12,
    }
    lower = answer.lower()
    # Chiffres arabes en priorité
    nums = _re.findall(r'\d+', answer)
    if nums:
        return int(nums[0])
    # Mots, du plus long au plus court pour éviter les sous-chaînes
    for word in sorted(word_map.keys(), key=len, reverse=True):
        if _re.search(r'\b' + word + r'\b', lower):
            return word_map[word]
    return 0

# ==============================
# ANALYSE PAR MOTS-CLES
# ==============================
def _normalize(s):
    import unicodedata
    return ''.join(
        c for c in unicodedata.normalize('NFD', s.lower())
        if unicodedata.category(c) != 'Mn'
    )

def analyze_with_keywords(question, answer):
    row = data[data["question"] == question]

    if row.empty:
        return {"note": 10, "verdict": "Question inconnue", "score": 0, "matched_keywords": 0}

    keywords    = row.iloc[0]["mots_cles"].split()
    answer_norm = _normalize(answer)
    match = sum(1 for kw in keywords if _normalize(kw) in answer_norm)
    score = match / len(keywords)
    note  = int(score * 20)

    if note >= 15:
        verdict = "Excellente réponse"
    elif note >= 10:
        verdict = "Bonne réponse"
    else:
        verdict = "Réponse faible"

    return {
        "note":             note,
        "verdict":          verdict,
        "score":            round(score, 2),
        "matched_keywords": match,
        "total_keywords":   len(keywords)
    }

# ==============================
# HEALTH CHECK
# ==============================
@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})

# ==============================
# CREATE SESSION
# ==============================
@app.route("/interview/create", methods=["POST"])
def create():
    sid = str(uuid.uuid4())
    sessions[sid] = {
        "current":    0,
        "score":      0,           # cumul des notes UNIQUEMENT des vraies questions (idx >= 3)
        "answers":    [],
        "specialite": "",
        "diplome":    "",
        "experience": 0,
        "created_at": time.time(), # BUG 11 FIX : pour le nettoyage automatique
    }
    return jsonify({"session_id": sid})

# ==============================
# GET QUESTION
# ==============================
@app.route("/interview/question/<int:n>", methods=["GET"])
def get_question(n):
    if n < 0 or n >= len(QUESTIONS):
        return jsonify({"error": "out of range"}), 400
    return jsonify({"question": QUESTIONS[n], "total": len(QUESTIONS)})

# ==============================
# ANSWER
# BUG 9 FIX : On n'ajoute plus les 3 premières questions (inscription)
#             au score total. Seules les vraies questions (idx >= 3)
#             contribuent au score. Le score_partiel reflète uniquement
#             la progression sur les questions évaluées.
# ==============================
@app.route("/interview/answer", methods=["POST"])
def answer():
    data_req = request.json or {}
    sid      = data_req.get("session_id")
    ans      = data_req.get("answer", "")

    session = sessions.get(sid)
    if not session:
        return jsonify({"error": "session not found"}), 404
    if session["current"] >= len(QUESTIONS):
        return jsonify({"error": "interview already complete"}), 400

    idx      = session["current"]
    question = QUESTIONS[idx]

    # ── Questions d'inscription : on enregistre sans noter ──
    if idx == 0:
        session["specialite"] = ans.strip().upper()
        result = {"message": "Spécialité enregistrée : " + session["specialite"],
                  "note": 0, "verdict": "Enregistré"}

    elif idx == 1:
        session["diplome"] = extract_diplome(ans)
        result = {"message": "Diplôme enregistré : " + session["diplome"],
                  "note": 0, "verdict": "Enregistré"}

    elif idx == 2:
        session["experience"] = extract_experience(ans)
        result = {"message": "Expérience enregistrée : " + str(session["experience"]) + " an(s)",
                  "note": 0, "verdict": "Enregistré"}

    # ── Vraies questions d'évaluation ──
    else:
        result = analyze_with_keywords(question, ans)
        session["score"] += result["note"]  # seulement ici

    session["answers"].append({"question": question, "answer": ans, "result": result})
    session["current"] += 1

    # Nombre de vraies questions évaluées jusqu'ici
    eval_questions = max(session["current"] - 3, 0)   # questions après les 3 d'inscription
    total_eval     = len(QUESTIONS) - 3                # nombre total de vraies questions
    if eval_questions > 0 and total_eval > 0:
        score_partiel = int((session["score"] / (total_eval * 20)) * 100)
    else:
        score_partiel = 0  # pas encore de vraie question évaluée

    result["score_partiel"] = score_partiel

    return jsonify(result)

# ==============================
# FINAL RESULT  (used by Symfony interviewFinaliser)
# ==============================
@app.route("/interview/result/<sid>", methods=["GET"])
def result(sid):
    session = sessions.get(sid)
    if not session:
        return jsonify({"error": "session not found"}), 404

    total_eval  = len(QUESTIONS) - 3
    score_final = int((session["score"] / (total_eval * 20)) * 100) if total_eval > 0 else 0

    accepted = (
        session["diplome"] in ["MD", "MPSY"]
        and session["experience"] >= 2
        and score_final >= 50
    )

    return jsonify({
        "score":      score_final,
        "accepted":   accepted,
        "specialite": session["specialite"],
        "diplome":    session["diplome"],
        "experience": session["experience"],
        "answers":    session["answers"]
    })

# ==============================
# FINALISE
# BUG 9 FIX : score calculé uniquement sur les vraies questions
# ==============================
@app.route("/interview/finalise", methods=["POST"])
def finalise():
    data_req = request.json or {}
    sid      = data_req.get("session_id")

    session = sessions.get(sid)
    if not session:
        return jsonify({"error": "session not found"}), 404
    if session["current"] < len(QUESTIONS):
        return jsonify({"error": "interview not complete yet",
                        "current": session["current"]}), 400

    # ── Score global (seulement sur vraies questions) ──────
    total_eval  = len(QUESTIONS) - 3
    score_final = int((session["score"] / (total_eval * 20)) * 100) if total_eval > 0 else 0

    # ── Conditions d'acceptation ──────────────────────────
    diplome_ok    = session["diplome"] in ["MD", "MPSY"]
    experience_ok = session["experience"] >= 2
    score_ok      = score_final >= 50
    accepted      = diplome_ok and experience_ok and score_ok

    # ── Raisons de refus ──────────────────────────────────
    refusal_reasons = []
    if not diplome_ok:
        refusal_reasons.append(
            f"Diplôme '{session['diplome']}' non reconnu (requis : MD ou MPSY)."
        )
    if not experience_ok:
        refusal_reasons.append(
            f"Expérience insuffisante ({session['experience']} an(s), minimum : 2 ans)."
        )
    if not score_ok:
        refusal_reasons.append(
            f"Score insuffisant ({score_final}/100, minimum : 50)."
        )

    # ── Détail par question ───────────────────────────────
    details = []
    for entry in session["answers"]:
        res = entry["result"]
        details.append({
            "question": entry["question"],
            "answer":   entry["answer"],
            "note":     res.get("note", 0),
            "note_max": 20,
            "verdict":  res.get("verdict", res.get("message", "")),
        })

    return jsonify({
        "status":          "accepted" if accepted else "refused",
        "accepted":        accepted,
        "score":           score_final,
        "specialite":      session["specialite"],
        "diplome":         session["diplome"],
        "experience":      session["experience"],
        "refusal_reasons": refusal_reasons,
        "details":         details,
        "criteria": {
            "diplome_ok":    diplome_ok,
            "experience_ok": experience_ok,
            "score_ok":      score_ok
        }
    })

# ==============================
# RUN
# ==============================
if __name__ == "__main__":
    app.run(port=5001, debug=True)