<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sleep_tracking')]
class SleepTracking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sleepTrackings')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(name: 'date_sommeil', type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateSommeil = null;

    #[ORM\Column(name: 'heure_coucher', type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureCoucher = null;

    #[ORM\Column(name: 'heure_reveil', type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureReveil = null;

    #[ORM\Column(name: 'duree_minutes')]
    private ?int $dureeMinutes = null;

    #[ORM\Column(name: 'qualite_sommeil')]
    private ?int $qualiteSommeil = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(name: 'date_creation', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->dateSommeil = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDateSommeil(): ?\DateTimeInterface
    {
        return $this->dateSommeil;
    }

    public function setDateSommeil(\DateTimeInterface $dateSommeil): static
    {
        $this->dateSommeil = $dateSommeil;

        return $this;
    }

    public function getHeureCoucher(): ?\DateTimeInterface
    {
        return $this->heureCoucher;
    }

    public function setHeureCoucher(\DateTimeInterface $heureCoucher): static
    {
        $this->heureCoucher = $heureCoucher;

        return $this;
    }

    public function getHeureReveil(): ?\DateTimeInterface
    {
        return $this->heureReveil;
    }

    public function setHeureReveil(\DateTimeInterface $heureReveil): static
    {
        $this->heureReveil = $heureReveil;

        return $this;
    }

    public function getDureeMinutes(): ?int
    {
        return $this->dureeMinutes;
    }

    public function setDureeMinutes(int $dureeMinutes): static
    {
        $this->dureeMinutes = $dureeMinutes;

        return $this;
    }

    public function getQualiteSommeil(): ?int
    {
        return $this->qualiteSommeil;
    }

    public function setQualiteSommeil(int $qualiteSommeil): static
    {
        $this->qualiteSommeil = $qualiteSommeil;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }
}
