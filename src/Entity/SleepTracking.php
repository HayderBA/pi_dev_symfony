<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\SleepTrackingRepository;

#[ORM\Entity(repositoryClass: SleepTrackingRepository::class)]
#[ORM\Table(name: 'sleep_tracking')]
class SleepTracking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sleepTrackings')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
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

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_sommeil = null;

    public function getDate_sommeil(): ?\DateTimeInterface
    {
        return $this->date_sommeil;
    }

    public function setDate_sommeil(\DateTimeInterface $date_sommeil): self
    {
        $this->date_sommeil = $date_sommeil;
        return $this;
    }

    #[ORM\Column(type: 'time', nullable: false)]
    private ?string $heure_coucher = null;

    public function getHeure_coucher(): ?string
    {
        return $this->heure_coucher;
    }

    public function setHeure_coucher(string $heure_coucher): self
    {
        $this->heure_coucher = $heure_coucher;
        return $this;
    }

    #[ORM\Column(type: 'time', nullable: false)]
    private ?string $heure_reveil = null;

    public function getHeure_reveil(): ?string
    {
        return $this->heure_reveil;
    }

    public function setHeure_reveil(string $heure_reveil): self
    {
        $this->heure_reveil = $heure_reveil;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $duree_minutes = null;

    public function getDuree_minutes(): ?int
    {
        return $this->duree_minutes;
    }

    public function setDuree_minutes(int $duree_minutes): self
    {
        $this->duree_minutes = $duree_minutes;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $qualite_sommeil = null;

    public function getQualite_sommeil(): ?int
    {
        return $this->qualite_sommeil;
    }

    public function setQualite_sommeil(int $qualite_sommeil): self
    {
        $this->qualite_sommeil = $qualite_sommeil;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $commentaire = null;

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_creation = null;

    public function getDate_creation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDate_creation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function getDateSommeil(): ?\DateTime
    {
        return $this->date_sommeil;
    }

    public function setDateSommeil(\DateTime $date_sommeil): static
    {
        $this->date_sommeil = $date_sommeil;

        return $this;
    }

    public function getHeureCoucher(): ?\DateTime
    {
        return $this->heure_coucher;
    }

    public function setHeureCoucher(\DateTime $heure_coucher): static
    {
        $this->heure_coucher = $heure_coucher;

        return $this;
    }

    public function getHeureReveil(): ?\DateTime
    {
        return $this->heure_reveil;
    }

    public function setHeureReveil(\DateTime $heure_reveil): static
    {
        $this->heure_reveil = $heure_reveil;

        return $this;
    }

    public function getDureeMinutes(): ?int
    {
        return $this->duree_minutes;
    }

    public function setDureeMinutes(int $duree_minutes): static
    {
        $this->duree_minutes = $duree_minutes;

        return $this;
    }

    public function getQualiteSommeil(): ?int
    {
        return $this->qualite_sommeil;
    }

    public function setQualiteSommeil(int $qualite_sommeil): static
    {
        $this->qualite_sommeil = $qualite_sommeil;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

}
