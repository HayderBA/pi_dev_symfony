<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\PsychologueRepository;

#[ORM\Entity(repositoryClass: PsychologueRepository::class)]
#[ORM\Table(name: 'psychologue')]
class Psychologue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idPsychologue = null;

    public function getIdPsychologue(): ?int
    {
        return $this->idPsychologue;
    }

    public function setIdPsychologue(int $idPsychologue): self
    {
        $this->idPsychologue = $idPsychologue;
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
    private ?string $prenom = null;

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $specialite = null;

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): self
    {
        $this->specialite = $specialite;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $diplome = null;

    public function getDiplome(): ?string
    {
        return $this->diplome;
    }

    public function setDiplome(string $diplome): self
    {
        $this->diplome = $diplome;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $experience = null;

    public function getExperience(): ?int
    {
        return $this->experience;
    }

    public function setExperience(int $experience): self
    {
        $this->experience = $experience;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $tarif = null;

    public function getTarif(): ?int
    {
        return $this->tarif;
    }

    public function setTarif(int $tarif): self
    {
        $this->tarif = $tarif;
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

    #[ORM\ManyToOne(targetEntity: Cabinet::class, inversedBy: 'psychologues')]
    #[ORM\JoinColumn(name: 'idCabinet', referencedColumnName: 'idCabinet')]
    private ?Cabinet $cabinet = null;

    public function getCabinet(): ?Cabinet
    {
        return $this->cabinet;
    }

    public function setCabinet(?Cabinet $cabinet): self
    {
        $this->cabinet = $cabinet;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Rendezvou::class, mappedBy: 'psychologue')]
    private Collection $rendezvous;

    public function __construct()
    {
        $this->rendezvous = new ArrayCollection();
    }

    /**
     * @return Collection<int, Rendezvou>
     */
    public function getRendezvous(): Collection
    {
        if (!$this->rendezvous instanceof Collection) {
            $this->rendezvous = new ArrayCollection();
        }
        return $this->rendezvous;
    }

    public function addRendezvou(Rendezvou $rendezvou): self
    {
        if (!$this->getRendezvous()->contains($rendezvou)) {
            $this->getRendezvous()->add($rendezvou);
        }
        return $this;
    }

    public function removeRendezvou(Rendezvou $rendezvou): self
    {
        $this->getRendezvous()->removeElement($rendezvou);
        return $this;
    }

}
