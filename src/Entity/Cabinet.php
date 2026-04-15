<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CabinetRepository;

#[ORM\Entity(repositoryClass: CabinetRepository::class)]
#[ORM\Table(name: 'cabinet')]
class Cabinet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idCabinet = null;

    public function getIdCabinet(): ?int
    {
        return $this->idCabinet;
    }

    public function setIdCabinet(int $idCabinet): self
    {
        $this->idCabinet = $idCabinet;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nomcabinet = null;

    public function getNomcabinet(): ?string
    {
        return $this->nomcabinet;
    }

    public function setNomcabinet(string $nomcabinet): self
    {
        $this->nomcabinet = $nomcabinet;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $adresse = null;

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $ville = null;

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $telephone = null;

    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    public function setTelephone(int $telephone): self
    {
        $this->telephone = $telephone;
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
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $status = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Psychologue::class, mappedBy: 'cabinet')]
    private Collection $psychologues;

    public function __construct()
    {
        $this->psychologues = new ArrayCollection();
    }

    /**
     * @return Collection<int, Psychologue>
     */
    public function getPsychologues(): Collection
    {
        if (!$this->psychologues instanceof Collection) {
            $this->psychologues = new ArrayCollection();
        }
        return $this->psychologues;
    }

    public function addPsychologue(Psychologue $psychologue): self
    {
        if (!$this->getPsychologues()->contains($psychologue)) {
            $this->getPsychologues()->add($psychologue);
        }
        return $this;
    }

    public function removePsychologue(Psychologue $psychologue): self
    {
        $this->getPsychologues()->removeElement($psychologue);
        return $this;
    }

}
