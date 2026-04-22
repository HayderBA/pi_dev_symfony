<?php

namespace App\Controller;

use App\Entity\Rendezvou;
use App\Repository\RendezvouRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Service\NtfyService;  // ← AJOUTER CETTE LIGNE

class PaiementController extends AbstractController
{
    // Afficher la page de paiement
    #[Route('/paiement/{id}', name: 'paiement_index')]
    public function index(Rendezvou $rdv, RendezvouRepository $repo): Response
    {
        // Récupérer le tarif du psychologue
        $idPsychologue = $rdv->getIdPsychologue();
        $montant = $repo->getTarifPsychologue($idPsychologue);
        
        return $this->render('front/paiement.html.twig', [
            'rdv' => $rdv,
            'montant' => $montant,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY']
        ]);
    }

    // Créer la session Stripe
    #[Route('/paiement/create-session/{id}', name: 'paiement_create_session')]
    public function createSession(Rendezvou $rdv, RendezvouRepository $repo, EntityManagerInterface $em): Response
    {
        // Configurer Stripe avec la clé secrète
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        
        // Récupérer le tarif du psychologue
        $idPsychologue = $rdv->getIdPsychologue();
        $montant = $repo->getTarifPsychologue($idPsychologue);
        
        // Créer la session de paiement Stripe
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $montant * 1000, // Stripe utilise les millimes
                    'product_data' => [
                        'name' => 'Consultation médicale',
                        'description' => 'Consultation du ' . $rdv->getDateRdv()->format('d/m/Y') . ' à ' . $rdv->getHeure()->format('H:i'),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('paiement_success', ['id' => $rdv->getIdRdv()], 0),
            'cancel_url' => $this->generateUrl('paiement_cancel', ['id' => $rdv->getIdRdv()], 0),
            'client_reference_id' => $rdv->getIdRdv(),
            'customer_email' => $rdv->getEmailPatient(),
        ]);
        
        // Sauvegarder les informations de paiement
        $rdv->setStripeSessionId($checkoutSession->id);
        $rdv->setMontant($montant);
        $em->flush();
        
        // Retourner l'ID de session pour le JavaScript
        return $this->json(['id' => $checkoutSession->id]);
    }

    // Paiement réussi + Envoi email + Notification ntfy
    #[Route('/paiement/success/{id}', name: 'paiement_success')]
    public function success(Rendezvou $rdv, EntityManagerInterface $em, MailerInterface $mailer, NtfyService $ntfy): Response  // ← AJOUTER NtfyService $ntfy
    {
        // Marquer le rendez-vous comme payé
        $rdv->setEstPaye(true);
        $rdv->setStatut('confirme');
        $em->flush();
        
        // ========== ENVOI D'EMAIL DE CONFIRMATION ==========
        $email = (new Email())
            ->from('no-reply@cabinet-medical.tn')
            ->to($rdv->getEmailPatient())
            ->subject('✅ Confirmation de paiement - Cabinet Médical')
            ->html($this->renderView('front/confirmation_paiement.html.twig', [
                'rdv' => $rdv,
                'montant' => $rdv->getMontant()
            ]));
        
        $mailer->send($email);
        // ===================================================
        
        // ========== ENVOI NOTIFICATION NTFY ==========
        $topic = 'cabinet_medical';
        $title = '✅ Rendez-vous confirmé';
        $message = "Bonjour " . $rdv->getPrenomPatient() . ",\nVotre rendez-vous du " . $rdv->getDateRdv()->format('d/m/Y') . " à " . $rdv->getHeure()->format('H:i') . " est confirmé.";
        
        $ntfy->send($topic, $title, $message);
        // ============================================
        
        $this->addFlash('success', '✅ Paiement effectué avec succès ! Un email de confirmation vous a été envoyé.');
        
        return $this->redirectToRoute('front_rendezvous_index');
    }

    // Paiement annulé
    #[Route('/paiement/cancel/{id}', name: 'paiement_cancel')]
    public function cancel(Rendezvou $rdv): Response
    {
        $this->addFlash('error', '❌ Paiement annulé. Vous pouvez réessayer.');
        
        return $this->redirectToRoute('paiement_index', ['id' => $rdv->getIdRdv()]);
    }
}