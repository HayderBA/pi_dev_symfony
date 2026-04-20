<?php
// src/Entity/Reservation.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
#[ORM\HasLifecycleCallbacks]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idReservation')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: 'idEvenement', referencedColumnName: 'idEvenement', nullable: false)]
    #[Assert\NotNull(message: "L'événement est obligatoire")]
    private ?Evenement $evenement = null;

    // 🔥 SUPPRIMÉ : utilisateur_id (plus nécessaire)

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Le nom doit contenir au moins 2 caractères", maxMessage: "Le nom ne peut pas dépasser 100 caractères")]
    #[Assert\Regex(pattern: "/^[a-zA-ZÀ-ÿ\s\-']+$/", message: "Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes")]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide")]
    #[Assert\Length(max: 100, maxMessage: "L'email ne peut pas dépasser 100 caractères")]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", message: "Format d'email invalide")]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le téléphone est obligatoire")]
    #[Assert\Length(min: 10, max: 15, minMessage: "Le téléphone doit contenir au moins 10 chiffres", maxMessage: "Le téléphone ne peut pas dépasser 15 chiffres")]
    #[Assert\Regex(pattern: "/^[0-9+\-\s\(\)]+$/", message: "Le téléphone contient des caractères non autorisés")]
    #[Assert\Regex(pattern: "/^(?:\+33|0)[1-9](?:[0-9]{8})$/", message: "Format de téléphone français invalide (ex: 0612345678 ou +33612345678)")]
    private ?string $telephone = null;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    #[Assert\Positive(message: "Le nombre de personnes doit être au moins 1")]
    #[Assert\LessThan(20, message: "Le nombre de personnes ne peut pas dépasser 20")]
    private ?int $nombrePersonnes = 1;

    #[ORM\Column(name: 'date_reservation', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Assert\NotNull(message: "La date de réservation est obligatoire")]
    #[Assert\LessThanOrEqual("today", message: "La date de réservation ne peut pas être dans le futur")]
    private ?\DateTimeInterface $dateReservation = null;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => 0])]
    #[Assert\Range(min: 0, max: 1, notInRangeMessage: "La probabilité de fraude doit être entre 0 et 1")]
    private ?float $fraudProbability = 0;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => 0])]
    private ?bool $isSuspicious = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $allocatedSeats = null;

    #[ORM\Column(length: 20, nullable: true, options: ['default' => 'auto'])]
    #[Assert\Choice(choices: ['auto', 'window', 'aisle', 'front', 'back'], message: "Préférence de siège invalide")]
    private ?string $seatingPreference = 'auto';

    // 🔥 QR CODE POUR LE BILLET
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $qrCode = null;

    // 🔥 NUMERO DE SIEGE (ex: A12, B5)
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $seatNumber = null;

    // ============ GETTERS ============
    public function getId(): ?int { return $this->id; }
    public function getEvenement(): ?Evenement { return $this->evenement; }
    public function getNom(): ?string { return $this->nom; }
    public function getEmail(): ?string { return $this->email; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getNombrePersonnes(): ?int { return $this->nombrePersonnes; }
    public function getDateReservation(): ?\DateTimeInterface { return $this->dateReservation; }
    public function getFraudProbability(): ?float { return $this->fraudProbability; }
    public function getIsSuspicious(): ?bool { return $this->isSuspicious; }
    public function getAllocatedSeats(): ?string { return $this->allocatedSeats; }
    public function getSeatingPreference(): ?string { return $this->seatingPreference; }
    public function getQrCode(): ?string { return $this->qrCode; }
    public function getSeatNumber(): ?string { return $this->seatNumber; }

    // ============ SETTERS ============
    public function setEvenement(?Evenement $evenement): self { $this->evenement = $evenement; return $this; }
    public function setNom(string $nom): self { $this->nom = trim(preg_replace('/\s+/', ' ', $nom)); return $this; }
    public function setEmail(string $email): self { $this->email = strtolower(trim($email)); return $this; }
    public function setTelephone(string $telephone): self { 
        $cleaned = preg_replace('/[^0-9+]/', '', $telephone);
        $this->telephone = $cleaned;
        return $this; 
    }
    public function setNombrePersonnes(int $nombrePersonnes): self { 
        $this->nombrePersonnes = max(1, min(20, $nombrePersonnes));
        return $this; 
    }
    public function setDateReservation(\DateTimeInterface $dateReservation): self { 
        $this->dateReservation = $dateReservation; 
        return $this; 
    }
    public function setFraudProbability(?float $fraudProbability): self { 
        $this->fraudProbability = min(1, max(0, $fraudProbability ?? 0));
        return $this; 
    }
    public function setIsSuspicious(?bool $isSuspicious): self { 
        $this->isSuspicious = $isSuspicious ?? false; 
        return $this; 
    }
    public function setAllocatedSeats(?string $allocatedSeats): self { 
        $this->allocatedSeats = $allocatedSeats; 
        return $this; 
    }
    public function setSeatingPreference(?string $seatingPreference): self { 
        $this->seatingPreference = $seatingPreference ?? 'auto'; 
        return $this; 
    }
    public function setQrCode(?string $qrCode): self { 
        $this->qrCode = $qrCode; 
        return $this; 
    }
    public function setSeatNumber(?string $seatNumber): self { 
        $this->seatNumber = $seatNumber; 
        return $this; 
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if ($this->dateReservation === null) {
            $this->dateReservation = new \DateTime();
        }
        // 🔥 SUPPRIMÉ : plus de setUtilisateurId
    }
    
    public function getMontantTotal(): float
    {
        return ($this->evenement?->getDynamicPrice() ?? 0) * $this->nombrePersonnes;
    }
}