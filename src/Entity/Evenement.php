<?php
// src/Entity/Evenement.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EvenementRepository;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idEvenement')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(min: 3, max: 100, minMessage: "Le titre doit contenir au moins 3 caractères", maxMessage: "Le titre ne peut pas dépasser 100 caractères")]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9\s\-éèêëàâôûç'’]+$/", message: "Le titre contient des caractères non autorisés")]
    private ?string $titre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(min: 10, max: 5000, minMessage: "La description doit contenir au moins 10 caractères", maxMessage: "La description ne peut pas dépasser 5000 caractères")]
    private ?string $description = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: "La date est obligatoire")]
    #[Assert\GreaterThan("today", message: "La date doit être dans le futur")]
    #[Assert\LessThan("+1 year", message: "La date ne peut pas être au-delà d'un an")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "La localisation est obligatoire")]
    #[Assert\Length(min: 3, max: 100, minMessage: "La localisation doit contenir au moins 3 caractères")]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9\s\-éèêëàâôûç',.]+$/", message: "La localisation contient des caractères non autorisés")]
    private ?string $localisation = null;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => 0])]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: "Le score de popularité doit être entre 0 et 100")]
    private ?float $popularityScore = 0;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 0])]
    #[Assert\PositiveOrZero(message: "La participation prédite doit être positive ou nulle")]
    #[Assert\LessThan(propertyPath: "maxCapacity", message: "La participation prédite ne peut pas dépasser la capacité maximale")]
    private ?int $predictedAttendance = 0;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => 50])]
    #[Assert\Positive(message: "Le prix doit être positif")]
    #[Assert\LessThan(500, message: "Le prix ne peut pas dépasser 500€")]
    private ?float $dynamicPrice = 50;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => 50])]
    #[Assert\PositiveOrZero(message: "Le prix de base doit être positif ou nul")]
    #[Assert\LessThan(500, message: "Le prix de base ne peut pas dépasser 500€")]
    private ?float $basePrice = 50;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 100])]
    #[Assert\NotBlank(message: "La capacité maximale est obligatoire")]
    #[Assert\Positive(message: "La capacité maximale doit être positive")]
    #[Assert\LessThan(1000, message: "La capacité maximale ne peut pas dépasser 1000 places")]
    private ?int $maxCapacity = 100;

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'standard'])]
    #[Assert\Choice(choices: ['standard', 'theatre', 'classroom', 'u-shape', 'conference'], message: "Type de disposition invalide")]
    private ?string $venueLayout = 'standard';

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Reservation::class, cascade: ['remove'])]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTitre(): ?string { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function getLocalisation(): ?string { return $this->localisation; }
    public function getPopularityScore(): ?float { return $this->popularityScore; }
    public function getPredictedAttendance(): ?int { return $this->predictedAttendance; }
    public function getDynamicPrice(): ?float { return $this->dynamicPrice; }
    public function getBasePrice(): ?float { return $this->basePrice; }
    public function getMaxCapacity(): ?int { return $this->maxCapacity; }
    public function getVenueLayout(): ?string { return $this->venueLayout; }
    public function getReservations(): Collection { return $this->reservations; }

    // Setters
    public function setTitre(string $titre): self { $this->titre = trim($titre); return $this; }
    public function setDescription(?string $description): self { $this->description = $description ? trim($description) : null; return $this; }
    public function setDate(\DateTimeInterface $date): self { $this->date = $date; return $this; }
    public function setLocalisation(string $localisation): self { $this->localisation = trim($localisation); return $this; }
    public function setPopularityScore(?float $popularityScore): self { $this->popularityScore = $popularityScore; return $this; }
    public function setPredictedAttendance(?int $predictedAttendance): self { $this->predictedAttendance = $predictedAttendance; return $this; }
    public function setDynamicPrice(?float $dynamicPrice): self { $this->dynamicPrice = $dynamicPrice; return $this; }
    public function setBasePrice(?float $basePrice): self { $this->basePrice = $basePrice; return $this; }
    public function setMaxCapacity(?int $maxCapacity): self { $this->maxCapacity = $maxCapacity; return $this; }
    public function setVenueLayout(?string $venueLayout): self { $this->venueLayout = $venueLayout; return $this; }

    // Méthodes utilitaires
    public function getCurrentReservationsCount(): int { return $this->reservations->count(); }
    
    public function getOccupancyRate(): float {
        if ($this->maxCapacity <= 0) return 0;
        return round(($this->getCurrentReservationsCount() / $this->maxCapacity) * 100, 2);
    }
    
    public function getStatus(): string {
        $now = new \DateTime();
        if ($this->date < $now) return 'Passé';
        if ($this->date->format('Y-m-d') == $now->format('Y-m-d')) return 'Aujourd\'hui';
        return 'À venir';
    }
    
    public function getStatusColor(): string {
        return match($this->getStatus()) {
            'Passé' => 'danger',
            'Aujourd\'hui' => 'success',
            default => 'primary'
        };
    }
    
    public function getPlacesRestantes(): int {
        return max(0, $this->maxCapacity - $this->getCurrentReservationsCount());
    }
    
    public function isComplet(): bool {
        return $this->getPlacesRestantes() <= 0;
    }
}