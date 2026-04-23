<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
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
    #[Assert\NotNull(message: "L'evenement est obligatoire")]
    private ?Evenement $evenement = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 100)]
    #[Assert\Regex(pattern: "/^[a-zA-ZÀ-ÿ\s\-']+$/", message: 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes')]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide")]
    #[Assert\Length(max: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le telephone est obligatoire')]
    #[Assert\Length(min: 8, max: 20)]
    #[Assert\Regex(pattern: '/^[0-9+\-\s\(\)]+$/', message: 'Le telephone contient des caracteres non autorises')]
    private ?string $telephone = null;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    #[Assert\Positive(message: 'Le nombre de personnes doit etre au moins 1')]
    #[Assert\LessThanOrEqual(20, message: 'Le nombre de personnes ne peut pas depasser 20')]
    private ?int $nombrePersonnes = 1;

    #[ORM\Column(name: 'date_reservation', type: 'datetime')]
    private ?\DateTimeInterface $dateReservation = null;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => 0])]
    #[Assert\Range(min: 0, max: 1)]
    private ?float $fraudProbability = 0;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => 0])]
    private ?bool $isSuspicious = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $allocatedSeats = null;

    #[ORM\Column(length: 20, nullable: true, options: ['default' => 'auto'])]
    #[Assert\Choice(choices: ['auto', 'window', 'aisle', 'front', 'back'])]
    private ?string $seatingPreference = 'auto';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $qrCode = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $seatNumber = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $solidarityAssociation = null;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => 0])]
    private ?float $solidarityAmount = 0;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $solidarityReceiptCode = null;

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
    public function getSolidarityAssociation(): ?string { return $this->solidarityAssociation; }
    public function getSolidarityAmount(): ?float { return $this->solidarityAmount ?? 0; }
    public function getSolidarityReceiptCode(): ?string { return $this->solidarityReceiptCode; }
    public function hasSolidarityContribution(): bool { return ($this->solidarityAmount ?? 0) > 0; }

    public function setEvenement(?Evenement $evenement): self { $this->evenement = $evenement; return $this; }
    public function setNom(string $nom): self { $this->nom = trim(preg_replace('/\s+/', ' ', $nom) ?? $nom); return $this; }
    public function setEmail(string $email): self { $this->email = strtolower(trim($email)); return $this; }
    public function setTelephone(string $telephone): self { $this->telephone = preg_replace('/[^0-9+]/', '', $telephone) ?? $telephone; return $this; }
    public function setNombrePersonnes(int $nombrePersonnes): self { $this->nombrePersonnes = max(1, min(20, $nombrePersonnes)); return $this; }
    public function setDateReservation(\DateTimeInterface $dateReservation): self { $this->dateReservation = $dateReservation; return $this; }
    public function setFraudProbability(?float $fraudProbability): self { $this->fraudProbability = min(1, max(0, $fraudProbability ?? 0)); return $this; }
    public function setIsSuspicious(?bool $isSuspicious): self { $this->isSuspicious = $isSuspicious ?? false; return $this; }
    public function setAllocatedSeats(?string $allocatedSeats): self { $this->allocatedSeats = $allocatedSeats; return $this; }
    public function setSeatingPreference(?string $seatingPreference): self { $this->seatingPreference = $seatingPreference ?? 'auto'; return $this; }
    public function setQrCode(?string $qrCode): self { $this->qrCode = $qrCode; return $this; }
    public function setSeatNumber(?string $seatNumber): self { $this->seatNumber = $seatNumber; return $this; }
    public function setSolidarityAssociation(?string $solidarityAssociation): self { $this->solidarityAssociation = $solidarityAssociation !== null ? trim($solidarityAssociation) : null; return $this; }
    public function setSolidarityAmount(?float $solidarityAmount): self { $this->solidarityAmount = max(0, (float) ($solidarityAmount ?? 0)); return $this; }
    public function setSolidarityReceiptCode(?string $solidarityReceiptCode): self { $this->solidarityReceiptCode = $solidarityReceiptCode !== null ? trim($solidarityReceiptCode) : null; return $this; }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if ($this->dateReservation === null) {
            $this->dateReservation = new \DateTime();
        }
    }

    public function getMontantTotal(): float
    {
        return (($this->evenement?->getDynamicPrice() ?? 0) * ($this->nombrePersonnes ?? 1)) + ($this->solidarityAmount ?? 0);
    }

    public function getTicketAmount(): float
    {
        return ($this->evenement?->getDynamicPrice() ?? 0) * ($this->nombrePersonnes ?? 1);
    }
}
