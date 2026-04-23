<?php

namespace App\Entity;

use App\Repository\CabinetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CabinetRepository::class)]
#[ORM\Table(name: 'cabinet')]
class Cabinet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idCabinet')]
    private ?int $idCabinet = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $nomcabinet = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $adresse = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $ville = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    private ?string $telephone = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 1000)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['actif', 'inactif'])]
    private ?string $status = 'actif';

    /**
     * @var Collection<int, Psychologue>
     */
    #[ORM\OneToMany(mappedBy: 'cabinet', targetEntity: Psychologue::class)]
    #[ORM\OrderBy(['nom' => 'ASC', 'prenom' => 'ASC'])]
    private Collection $psychologues;

    public function __construct()
    {
        $this->psychologues = new ArrayCollection();
    }

    public function getIdCabinet(): ?int
    {
        return $this->idCabinet;
    }

    public function getId(): ?int
    {
        return $this->idCabinet;
    }

    public function getNomcabinet(): ?string
    {
        return $this->nomcabinet;
    }

    public function setNomcabinet(string $nomcabinet): static
    {
        $this->nomcabinet = $nomcabinet;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Psychologue>
     */
    public function getPsychologues(): Collection
    {
        return $this->psychologues;
    }

    public function getPsychologuesCount(): int
    {
        return $this->psychologues->count();
    }
}
