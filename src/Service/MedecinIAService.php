<?php

namespace App\Service;

class MedecinIAService
{
    public function getReponse(string $message): string
    {
        $message = strtolower(trim($message));
        
        // Mots-clés et réponses simples
        if (strpos($message, 'stress') !== false || strpos($message, 'anxiete') !== false || strpos($message, 'anxiété') !== false) {
            return "Respirez profondément 5 fois. Inspirez par le nez, expirez par la bouche. Cela va vous aider à vous calmer. Voulez-vous d'autres techniques ?";
        }
        
        if (strpos($message, 'triste') !== false || strpos($message, 'deprime') !== false || strpos($message, 'déprime') !== false) {
            return "Je comprends votre tristesse. Parler aide. Voulez-vous en parler ? Je suis là pour vous écouter.";
        }
        
        if (strpos($message, 'dormir') !== false || strpos($message, 'insomnie') !== false || strpos($message, 'sommeil') !== false) {
            return "Pour mieux dormir : couchez-vous à heure fixe, éteignez les écrans 1h avant. Besoin de plus de conseils ?";
        }
        
        if (strpos($message, 'fatigue') !== false || strpos($message, 'fatigué') !== false) {
            return "La fatigue peut être liée au stress ou au sommeil. Reposez-vous, buvez de l'eau. Comment vous sentez-vous ?";
        }
        
        if (strpos($message, 'confiance') !== false || strpos($message, 'estime') !== false) {
            return "Vous avez de la valeur. Chaque jour, notez une petite réussite. Cela aide à renforcer la confiance.";
        }
        
        if (strpos($message, 'seul') !== false || strpos($message, 'solitude') !== false) {
            return "Vous n'êtes pas seul. Rejoignez notre forum, parlez avec d'autres personnes. Je suis là pour vous.";
        }
        
        if (strpos($message, 'travail') !== false || strpos($message, 'burnout') !== false) {
            return "Prenez soin de vous. Fixez des limites, reposez-vous. Parlez-en à votre médecin si besoin.";
        }
        
        if (strpos($message, 'manger') !== false || strpos($message, 'repas') !== false || strpos($message, 'pizza') !== false) {
            return "Manger équilibré, c'est important. Un petit écart n'est pas grave. L'essentiel est l'équilibre sur la semaine.";
        }
        
        if (strpos($message, 'sport') !== false || strpos($message, 'marcher') !== false) {
            return "L'activité physique est excellente pour le moral. 30 minutes de marche par jour suffisent. Essayez !";
        }
        
        if (strpos($message, 'rdv') !== false || strpos($message, 'rendez-vous') !== false || strpos($message, 'docteur') !== false) {
            return "Pour prendre rendez-vous, allez dans la section Rendez-vous du menu. Un médecin vous attend.";
        }
        
        if (strpos($message, 'inscription') !== false || strpos($message, 'compte') !== false) {
            return "Pour vous inscrire, cliquez sur Inscription dans le menu. C'est gratuit et rapide.";
        }
        
        if (strpos($message, 'ressource') !== false || strpos($message, 'article') !== false) {
            return "Les ressources sont disponibles dans le menu Plus puis Ressources. Vous y trouverez des conseils utiles.";
        }
        
        if (strpos($message, 'test') !== false || strpos($message, 'quiz') !== false) {
            return "Les tests psychologiques vous aident à évaluer votre état. Allez dans la section Tests du menu.";
        }
        
        if (strpos($message, 'video') !== false || strpos($message, 'méditation') !== false) {
            return "Des vidéos de relaxation sont disponibles dans Ressources. Elles vous aideront à vous détendre.";
        }
        
        if (strpos($message, 'message') !== false || strpos($message, 'messagerie') !== false) {
            return "La messagerie privée vous permet d'échanger avec votre médecin. Cliquez sur Messages dans le menu.";
        }
        
        if (strpos($message, 'bonjour') !== false || strpos($message, 'salut') !== false || strpos($message, 'coucou') !== false) {
            return "Bonjour ! Comment puis-je vous aider aujourd'hui ? Parlez-moi de ce qui vous préoccupe.";
        }
        
        // Si aucun sujet trouvé
        return "Désolé, je ne peux pas répondre à cette question. Parlez-moi de stress, tristesse, sommeil, ou consultez le menu GrowMind.";
    }
    
    public function isUrgent(string $message): bool
    {
        $message = strtolower($message);
        $urgentMots = ['suicide', 'mourir', 'urgence vitale', 'hopital', 'ambulance'];
        foreach ($urgentMots as $mot) {
            if (strpos($message, $mot) !== false) return true;
        }
        return false;
    }
}