<?php
// src/Controller/ReservationController.php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\EvenementRepository;
use App\Repository\ReservationRepository;
use App\Service\QrCodeService;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    // 🔥 Route AJAX pour récupérer les sièges disponibles (plan interactif)
    #[Route('/reservation/seats/{id}', name: 'app_reservation_seats', methods: ['GET'])]
    public function getSeats(int $id, EvenementRepository $eventRepo): JsonResponse
    {
        $evenement = $eventRepo->find($id);
        if (!$evenement) {
            return $this->json(['error' => 'Événement non trouvé'], 404);
        }
        
        return $this->json([
            'grid' => $evenement->getSeatsGrid(),
            'availableSeats' => $evenement->getAvailableSeats(),
            'maxCapacity' => $evenement->getMaxCapacity(),
            'currentReservations' => $evenement->getCurrentReservationsCount()
        ]);
    }
    
    #[Route('/reservation/new/{id}', name: 'app_reservation_new', methods: ['POST'])]
    public function new(
        int $id, 
        Request $request, 
        EvenementRepository $eventRepo, 
        EntityManagerInterface $em,
        QrCodeService $qrCodeService,
        EmailService $emailService
    ): Response
    {
        $evenement = $eventRepo->find($id);
        
        if (!$evenement) {
            $this->addFlash('error', 'Événement non trouvé');
            return $this->redirectToRoute('app_evenement_list');
        }
        
        // Vérification du token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('reservation' . $id, $submittedToken)) {
            $this->addFlash('error', 'Token invalide');
            return $this->redirectToRoute('app_evenement_show', ['id' => $id]);
        }
        
        if ($evenement->getDate() < new \DateTime()) {
            $this->addFlash('error', 'Cet événement est déjà passé');
            return $this->redirectToRoute('app_evenement_show', ['id' => $id]);
        }
        
        $nom = trim($request->request->get('nom'));
        $email = trim($request->request->get('email'));
        $telephone = trim($request->request->get('telephone'));
        $nombrePersonnes = (int)$request->request->get('nombre_personnes', 1);
        $selectedSeat = $request->request->get('selected_seat');
        
        // Validations
        if (empty($nom) || strlen($nom) < 2) {
            $this->addFlash('error', 'Le nom doit contenir au moins 2 caractères');
            return $this->redirectToRoute('app_evenement_show', ['id' => $id]);
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Email invalide');
            return $this->redirectToRoute('app_evenement_show', ['id' => $id]);
        }
        
        $digits = preg_replace('/[^0-9]/', '', $telephone);
        if (strlen($digits) != 10) {
            $this->addFlash('error', 'Le téléphone doit contenir 10 chiffres');
            return $this->redirectToRoute('app_evenement_show', ['id' => $id]);
        }
        
        if ($nombrePersonnes < 1) {
            $this->addFlash('error', 'Le nombre de places doit être au moins 1');
            return $this->redirectToRoute('app_evenement_show', ['id' => $id]);
        }
        
        $placesDisponibles = $evenement->getMaxCapacity() - $evenement->getCurrentReservationsCount();
        if ($nombrePersonnes > $placesDisponibles) {
            $this->addFlash('error', 'Nombre de places insuffisant (maximum ' . $placesDisponibles . ')');
            return $this->redirectToRoute('app_evenement_show', ['id' => $id]);
        }
        
        // Vérifier que le siège sélectionné est disponible
        if ($selectedSeat) {
            $availableSeats = $evenement->getAvailableSeats();
            if (!in_array($selectedSeat, $availableSeats)) {
                $this->addFlash('error', 'Ce siège n\'est plus disponible');
                return $this->redirectToRoute('app_evenement_show', ['id' => $id]);
            }
        }
        
        // Attribution automatique des sièges
        $seatsToAssign = [];
        if ($selectedSeat) {
            $availableSeats = array_values(array_filter(
                $evenement->getAvailableSeats(),
                static fn (string $seat): bool => $seat !== $selectedSeat
            ));
            $seatsToAssign = array_merge([$selectedSeat], array_slice($availableSeats, 0, max(0, $nombrePersonnes - 1)));
        } else {
            $availableSeats = $evenement->getAvailableSeats();
            $seatsToAssign = array_slice($availableSeats, 0, $nombrePersonnes);
        }

        if (count($seatsToAssign) < $nombrePersonnes) {
            $this->addFlash('error', 'Impossible d\'attribuer suffisamment de sièges disponibles');
            return $this->redirectToRoute('app_evenement_show', ['id' => $id]);
        }
        
        $reservationsCrees = [];
        
        for ($i = 0; $i < $nombrePersonnes; $i++) {
            $reservation = new Reservation();
            $reservation->setEvenement($evenement);
            $reservation->setNom($nom);
            $reservation->setEmail($email);
            $reservation->setTelephone($digits);
            $reservation->setNombrePersonnes(1);
            $reservation->setDateReservation(new \DateTime());
            
            // 🔥 SUPPRIMÉ : setUtilisateurId(1)
            
            // Attribuer le numéro de siège
            $seatNumber = $seatsToAssign[$i] ?? null;
            $reservation->setSeatNumber($seatNumber);
            
            // Générer le QR code
            $qrData = json_encode([
                'reservation_id' => uniqid('resa_', true),
                'event_id' => $evenement->getId(),
                'event_title' => $evenement->getTitre(),
                'seat' => $seatNumber,
                'nom' => $nom,
                'date' => $evenement->getDate()->format('Y-m-d H:i')
            ]);
            $qrCode = $qrCodeService->generate($qrData, $reservation);
            $reservation->setQrCode($qrCode);
            
            $em->persist($reservation);
            $reservationsCrees[] = $reservation;
        }
        
        $em->flush();
        
        // Envoyer l'email de confirmation avec QR code
        if (!empty($reservationsCrees)) {
            $emailService->sendReservationConfirmation($reservationsCrees[0]);
        }
        
        $this->addFlash('success', '✓ ' . $nombrePersonnes . ' réservation(s) effectuée(s) avec succès ! Un email avec votre billet vous a été envoyé.');
        return $this->redirectToRoute('app_reservation_success');
    }
    
    #[Route('/reservation/success', name: 'app_reservation_success')]
    public function success(): Response
    {
        return $this->render('front/reservation_success.html.twig');
    }
    
    #[Route('/admin/reservation/{id}/delete', name: 'app_reservation_admin_delete', methods: ['POST'])]
    public function delete(Request $request, $id, ReservationRepository $repo, EntityManagerInterface $em): Response
    {
        $reservation = $repo->find($id);
        if ($reservation && $this->isCsrfTokenValid('delete_reservation_' . $id, $request->request->get('_token'))) {
            $em->remove($reservation);
            $em->flush();
            $this->addFlash('success', 'Réservation supprimée avec succès');
        }
        return $this->redirect($request->headers->get('referer'));
    }
}
