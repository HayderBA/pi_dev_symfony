<?php

namespace App\Controller;

use App\Entity\Rendezvous;
use App\Service\CabinetMailerService;
use App\Service\NtfyService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaiementController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(default::STRIPE_PUBLIC_KEY)%')]
        private readonly ?string $stripePublicKey,
        #[Autowire('%env(default::STRIPE_SECRET_KEY)%')]
        private readonly ?string $stripeSecretKey
    ) {
    }

    #[Route('/paiement/{id}', name: 'paiement_index')]
    public function index(Rendezvous $rdv): Response
    {
        if ($rdv->isEstPaye()) {
            $this->addFlash('success', 'Ce rendez-vous est deja paye.');

            return $this->redirectToRoute('front_rendezvous_show', ['id' => $rdv->getIdRdv()]);
        }

        return $this->render('front/paiement.html.twig', [
            'rdv' => $rdv,
            'montant' => $rdv->getTarifConsultation() ?: $rdv->getPsychologue()?->getTarif(),
            'stripe_public_key' => $this->stripePublicKey,
            'stripe_ready' => '' !== trim((string) $this->stripePublicKey) && '' !== trim((string) $this->stripeSecretKey),
        ]);
    }

    #[Route('/paiement/create-session/{id}', name: 'paiement_create_session')]
    public function createSession(Rendezvous $rdv, EntityManagerInterface $entityManager): JsonResponse
    {
        if ('' === trim((string) $this->stripeSecretKey) || '' === trim((string) $this->stripePublicKey)) {
            return $this->json(['error' => 'Stripe n est pas configure. Ajoutez STRIPE_PUBLIC_KEY et STRIPE_SECRET_KEY.'], 400);
        }

        $montant = $rdv->getTarifConsultation() ?: $rdv->getPsychologue()?->getTarif();
        if (!$montant) {
            return $this->json(['error' => 'Montant indisponible pour ce rendez-vous.'], 400);
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) round($montant * 100),
                    'product_data' => [
                        'name' => 'Consultation GrowMind',
                        'description' => sprintf(
                            '%s - %s a %s',
                            $rdv->getPsychologue()?->getDisplayName() ?: 'Psychologue',
                            $rdv->getDateRdv()?->format('d/m/Y'),
                            $rdv->getHeure()?->format('H:i')
                        ),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('paiement_success', ['id' => $rdv->getIdRdv()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('paiement_cancel', ['id' => $rdv->getIdRdv()], UrlGeneratorInterface::ABSOLUTE_URL),
            'client_reference_id' => (string) $rdv->getIdRdv(),
            'customer_email' => $rdv->getEmailPatient(),
        ]);

        $rdv->setStripeSessionId($checkoutSession->id);
        $rdv->setMontant((float) $montant);
        $entityManager->flush();

        return $this->json(['id' => $checkoutSession->id]);
    }

    #[Route('/paiement/success/{id}', name: 'paiement_success')]
    public function success(Rendezvous $rdv, EntityManagerInterface $entityManager, CabinetMailerService $mailerService, NtfyService $ntfyService): Response
    {
        $rdv->setEstPaye(true);
        $rdv->setStatut('confirme');
        $entityManager->flush();

        $mailerService->sendPaymentConfirmation($rdv);
        $ntfyService->send(
            'cabinet_medical',
            'Rendez-vous confirme',
            sprintf(
                "Bonjour %s,\nVotre rendez-vous du %s a %s est confirme.",
                $rdv->getPrenomPatient() ?: 'patient',
                $rdv->getDateRdv()?->format('d/m/Y'),
                $rdv->getHeure()?->format('H:i')
            )
        );

        $this->addFlash('success', 'Paiement effectue avec succes. Une confirmation a ete envoyee si un email est disponible.');

        return $this->redirectToRoute('front_rendezvous_show', ['id' => $rdv->getIdRdv()]);
    }

    #[Route('/paiement/cancel/{id}', name: 'paiement_cancel')]
    public function cancel(Rendezvous $rdv): Response
    {
        $this->addFlash('error', 'Paiement annule. Vous pouvez reessayer quand vous voulez.');

        return $this->redirectToRoute('front_rendezvous_show', ['id' => $rdv->getIdRdv()]);
    }
}
