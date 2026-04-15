<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DoctorRepository;

#[ORM\Entity(repositoryClass: DoctorRepository::class)]
#[ORM\Table(name: 'doctors')]
class Doctor
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'doctors')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $specialty = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $experience = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $diplome = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $disponible = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $actif = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tarif_consultation = null;

    // =========================
    // GETTERS & SETTERS
    // =========================


    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    public function setSpecialty(?string $specialty): self
    {
        $this->specialty = $specialty;
        return $this;
    }

    public function getExperience(): ?int
    {
        return $this->experience;
    }

    public function setExperience(?int $experience): self
    {
        $this->experience = $experience;
        return $this;
    }

    public function getDiplome(): ?string
    {
        return $this->diplome;
    }

    public function setDiplome(?string $diplome): self
    {
        $this->diplome = $diplome;
        return $this;
    }

    public function getDisponible(): ?int
    {
        return $this->disponible;
    }

    public function setDisponible(?int $disponible): self
    {
        $this->disponible = $disponible;
        return $this;
    }

    public function getActif(): ?int
    {
        return $this->actif;
    }

    public function setActif(?int $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    public function getTarifConsultation(): ?float
    {
        return $this->tarif_consultation;
    }

    public function setTarifConsultation(?float $tarif_consultation): self
    {
        $this->tarif_consultation = $tarif_consultation;
        return $this;
    }
}