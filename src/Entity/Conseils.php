<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'conseils')]
class Conseils
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'type_etat', length: 50)]
    private ?string $typeEtat = null;

    #[ORM\Column(nullable: true, options: ['comment' => 'Niveau de gravite ou categorie'])]
    private ?int $niveau = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $conseil = null;

    #[ORM\Column(length: 50, nullable: true, options: ['comment' => 'sante mentale, physique, nutrition, etc.'])]
    private ?string $categorie = null;

    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'conseils')]
    #[ORM\JoinTable(name: 'conseils_utilisateurs')]
    #[ORM\JoinColumn(name: 'conseil_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'utilisateur_id', referencedColumnName: 'id')]
    private Collection $utilisateurs;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeEtat(): ?string
    {
        return $this->typeEtat;
    }

    public function setTypeEtat(string $typeEtat): static
    {
        $this->typeEtat = $typeEtat;

        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(?int $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getConseil(): ?string
    {
        return $this->conseil;
    }

    public function setConseil(string $conseil): static
    {
        $this->conseil = $conseil;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (! $this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        $this->utilisateurs->removeElement($utilisateur);

        return $this;
    }
}
