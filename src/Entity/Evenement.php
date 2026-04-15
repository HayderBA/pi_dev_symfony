<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EvenementRepository;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idEvenement = null;

    public function getIdEvenement(): ?int
    {
        return $this->idEvenement;
    }

    public function setIdEvenement(int $idEvenement): self
    {
        $this->idEvenement = $idEvenement;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $titre = null;

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date = null;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $localisation = null;

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $popularity_score = null;

    public function getPopularity_score(): ?float
    {
        return $this->popularity_score;
    }

    public function setPopularity_score(?float $popularity_score): self
    {
        $this->popularity_score = $popularity_score;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $predicted_attendance = null;

    public function getPredicted_attendance(): ?int
    {
        return $this->predicted_attendance;
    }

    public function setPredicted_attendance(?int $predicted_attendance): self
    {
        $this->predicted_attendance = $predicted_attendance;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $dynamic_price = null;

    public function getDynamic_price(): ?float
    {
        return $this->dynamic_price;
    }

    public function setDynamic_price(?float $dynamic_price): self
    {
        $this->dynamic_price = $dynamic_price;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $base_price = null;

    public function getBase_price(): ?float
    {
        return $this->base_price;
    }

    public function setBase_price(?float $base_price): self
    {
        $this->base_price = $base_price;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $max_capacity = null;

    public function getMax_capacity(): ?int
    {
        return $this->max_capacity;
    }

    public function setMax_capacity(?int $max_capacity): self
    {
        $this->max_capacity = $max_capacity;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $venue_layout = null;

    public function getVenue_layout(): ?string
    {
        return $this->venue_layout;
    }

    public function setVenue_layout(?string $venue_layout): self
    {
        $this->venue_layout = $venue_layout;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'evenement')]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        if (!$this->reservations instanceof Collection) {
            $this->reservations = new ArrayCollection();
        }
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->getReservations()->contains($reservation)) {
            $this->getReservations()->add($reservation);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        $this->getReservations()->removeElement($reservation);
        return $this;
    }

    public function getPopularityScore(): ?string
    {
        return $this->popularity_score;
    }

    public function setPopularityScore(?string $popularity_score): static
    {
        $this->popularity_score = $popularity_score;

        return $this;
    }

    public function getPredictedAttendance(): ?int
    {
        return $this->predicted_attendance;
    }

    public function setPredictedAttendance(?int $predicted_attendance): static
    {
        $this->predicted_attendance = $predicted_attendance;

        return $this;
    }

    public function getDynamicPrice(): ?string
    {
        return $this->dynamic_price;
    }

    public function setDynamicPrice(?string $dynamic_price): static
    {
        $this->dynamic_price = $dynamic_price;

        return $this;
    }

    public function getBasePrice(): ?string
    {
        return $this->base_price;
    }

    public function setBasePrice(?string $base_price): static
    {
        $this->base_price = $base_price;

        return $this;
    }

    public function getMaxCapacity(): ?int
    {
        return $this->max_capacity;
    }

    public function setMaxCapacity(?int $max_capacity): static
    {
        $this->max_capacity = $max_capacity;

        return $this;
    }

    public function getVenueLayout(): ?string
    {
        return $this->venue_layout;
    }

    public function setVenueLayout(?string $venue_layout): static
    {
        $this->venue_layout = $venue_layout;

        return $this;
    }

}
