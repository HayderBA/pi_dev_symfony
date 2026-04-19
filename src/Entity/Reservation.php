<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReservationRepository;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idReservation = null;

    public function getIdReservation(): ?int
    {
        return $this->idReservation;
    }

    public function setIdReservation(int $idReservation): self
    {
        $this->idReservation = $idReservation;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: 'idEvenement', referencedColumnName: 'idEvenement')]
    private ?Evenement $evenement = null;

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id')]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $telephone = null;

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nombre_personnes = null;

    public function getNombre_personnes(): ?int
    {
        return $this->nombre_personnes;
    }

    public function setNombre_personnes(?int $nombre_personnes): self
    {
        $this->nombre_personnes = $nombre_personnes;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_reservation = null;

    public function getDate_reservation(): ?\DateTimeInterface
    {
        return $this->date_reservation;
    }

    public function setDate_reservation(?\DateTimeInterface $date_reservation): self
    {
        $this->date_reservation = $date_reservation;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $fraud_probability = null;

    public function getFraud_probability(): ?float
    {
        return $this->fraud_probability;
    }

    public function setFraud_probability(?float $fraud_probability): self
    {
        $this->fraud_probability = $fraud_probability;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $is_suspicious = null;

    public function getIs_suspicious(): ?int
    {
        return $this->is_suspicious;
    }

    public function setIs_suspicious(?int $is_suspicious): self
    {
        $this->is_suspicious = $is_suspicious;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $allocated_seats = null;

    public function getAllocated_seats(): ?string
    {
        return $this->allocated_seats;
    }

    public function setAllocated_seats(?string $allocated_seats): self
    {
        $this->allocated_seats = $allocated_seats;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $seating_preference = null;

    public function getSeating_preference(): ?string
    {
        return $this->seating_preference;
    }

    public function setSeating_preference(?string $seating_preference): self
    {
        $this->seating_preference = $seating_preference;
        return $this;
    }

    public function getNombrePersonnes(): ?int
    {
        return $this->nombre_personnes;
    }

    public function setNombrePersonnes(?int $nombre_personnes): static
    {
        $this->nombre_personnes = $nombre_personnes;

        return $this;
    }

    public function getDateReservation(): ?\DateTime
    {
        return $this->date_reservation;
    }

    public function setDateReservation(?\DateTime $date_reservation): static
    {
        $this->date_reservation = $date_reservation;

        return $this;
    }

    public function getFraudProbability(): ?string
    {
        return $this->fraud_probability;
    }

    public function setFraudProbability(?string $fraud_probability): static
    {
        $this->fraud_probability = $fraud_probability;

        return $this;
    }

    public function getIsSuspicious(): ?int
    {
        return $this->is_suspicious;
    }

    public function setIsSuspicious(?int $is_suspicious): static
    {
        $this->is_suspicious = $is_suspicious;

        return $this;
    }

    public function getAllocatedSeats(): ?string
    {
        return $this->allocated_seats;
    }

    public function setAllocatedSeats(?string $allocated_seats): static
    {
        $this->allocated_seats = $allocated_seats;

        return $this;
    }

    public function getSeatingPreference(): ?string
    {
        return $this->seating_preference;
    }

    public function setSeatingPreference(?string $seating_preference): static
    {
        $this->seating_preference = $seating_preference;

        return $this;
    }

}
