<?php

namespace App\Entity;

use App\Repository\AlerteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlerteRepository::class)]
#[ORM\Table(name: 'alerte')]
class Alerte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $distance = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 8)]
    private ?string $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8)]
    private ?string $longitude = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'envoyee';

    #[ORM\Column(length: 100)]
    private ?string $patientNom = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $medecin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $patient = null;

    #[ORM\Column(length: 100)]
    private ?string $medecinNom = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getDistance(): ?string { return $this->distance; }
    public function setDistance(string $distance): static { $this->distance = $distance; return $this; }
    public function getLatitude(): ?float { return $this->latitude !== null ? (float) $this->latitude : null; }
    public function setLatitude(float $latitude): static { $this->latitude = (string) $latitude; return $this; }
    public function getLongitude(): ?float { return $this->longitude !== null ? (float) $this->longitude : null; }
    public function setLongitude(float $longitude): static { $this->longitude = (string) $longitude; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getPatientNom(): ?string { return $this->patientNom; }
    public function setPatientNom(string $patientNom): static { $this->patientNom = $patientNom; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
    public function getMedecin(): ?User { return $this->medecin; }
    public function setMedecin(?User $medecin): static { $this->medecin = $medecin; return $this; }
    public function getPatient(): ?User { return $this->patient; }
    public function setPatient(?User $patient): static { $this->patient = $patient; return $this; }
    public function getMedecinNom(): ?string { return $this->medecinNom; }
    public function setMedecinNom(string $medecinNom): static { $this->medecinNom = $medecinNom; return $this; }
}
