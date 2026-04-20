<?php

namespace App\Entity;

use App\Repository\AlerteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlerteRepository::class)]
class Alerte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $medecin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $patient = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $distance = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8)]
    private ?string $longitude = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'envoyee';

    #[ORM\Column(length: 100)]
    private ?string $medecinNom = null;

    #[ORM\Column(length: 100)]
    private ?string $patientNom = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getMedecin(): ?User { return $this->medecin; }
    public function setMedecin(?User $medecin): static { $this->medecin = $medecin; return $this; }
    public function getPatient(): ?User { return $this->patient; }
    public function setPatient(?User $patient): static { $this->patient = $patient; return $this; }
    public function getDistance(): ?string { return $this->distance; }
    public function setDistance(string $distance): static { $this->distance = $distance; return $this; }
    public function getLatitude(): ?string { return $this->latitude; }
    public function setLatitude(string $latitude): static { $this->latitude = $latitude; return $this; }
    public function getLongitude(): ?string { return $this->longitude; }
    public function setLongitude(string $longitude): static { $this->longitude = $longitude; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getMedecinNom(): ?string { return $this->medecinNom; }
    public function setMedecinNom(string $medecinNom): static { $this->medecinNom = $medecinNom; return $this; }
    public function getPatientNom(): ?string { return $this->patientNom; }
    public function setPatientNom(string $patientNom): static { $this->patientNom = $patientNom; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
}