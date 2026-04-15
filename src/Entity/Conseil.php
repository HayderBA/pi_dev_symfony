<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ConseilRepository;

#[ORM\Entity(repositoryClass: ConseilRepository::class)]
#[ORM\Table(name: 'conseils')]
class Conseil
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type_etat = null;

    public function getType_etat(): ?string
    {
        return $this->type_etat;
    }

    public function setType_etat(string $type_etat): self
    {
        $this->type_etat = $type_etat;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $niveau = null;

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(?int $niveau): self
    {
        $this->niveau = $niveau;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $conseil = null;

    public function getConseil(): ?string
    {
        return $this->conseil;
    }

    public function setConseil(string $conseil): self
    {
        $this->conseil = $conseil;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $categorie = null;

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ConseilsUtilisateur::class, mappedBy: 'conseil')]
    private Collection $conseilsUtilisateurs;

    public function __construct()
    {
        $this->conseilsUtilisateurs = new ArrayCollection();
    }

    /**
     * @return Collection<int, ConseilsUtilisateur>
     */
    public function getConseilsUtilisateurs(): Collection
    {
        if (!$this->conseilsUtilisateurs instanceof Collection) {
            $this->conseilsUtilisateurs = new ArrayCollection();
        }
        return $this->conseilsUtilisateurs;
    }

    public function addConseilsUtilisateur(ConseilsUtilisateur $conseilsUtilisateur): self
    {
        if (!$this->getConseilsUtilisateurs()->contains($conseilsUtilisateur)) {
            $this->getConseilsUtilisateurs()->add($conseilsUtilisateur);
        }
        return $this;
    }

    public function removeConseilsUtilisateur(ConseilsUtilisateur $conseilsUtilisateur): self
    {
        $this->getConseilsUtilisateurs()->removeElement($conseilsUtilisateur);
        return $this;
    }

    public function getTypeEtat(): ?string
    {
        return $this->type_etat;
    }

    public function setTypeEtat(string $type_etat): static
    {
        $this->type_etat = $type_etat;

        return $this;
    }

}
