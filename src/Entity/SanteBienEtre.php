<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\SanteBienEtreRepository;

#[ORM\Entity(repositoryClass: SanteBienEtreRepository::class)]
#[ORM\Table(name: 'sante_bien_etre')]
class SanteBienEtre
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'santeBienEtres')]
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $humeur = null;

    public function getHumeur(): ?string
    {
        return $this->humeur;
    }

    public function setHumeur(string $humeur): self
    {
        $this->humeur = $humeur;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $niveau_stress = null;

    public function getNiveau_stress(): ?int
    {
        return $this->niveau_stress;
    }

    public function setNiveau_stress(int $niveau_stress): self
    {
        $this->niveau_stress = $niveau_stress;
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
    private ?string $nutrition = null;

    public function getNutrition(): ?string
    {
        return $this->nutrition;
    }

    public function setNutrition(?string $nutrition): self
    {
        $this->nutrition = $nutrition;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $activite_physique = null;

    public function getActivite_physique(): ?string
    {
        return $this->activite_physique;
    }

    public function setActivite_physique(?string $activite_physique): self
    {
        $this->activite_physique = $activite_physique;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $developpement_personnel = null;

    public function getDeveloppement_personnel(): ?string
    {
        return $this->developpement_personnel;
    }

    public function setDeveloppement_personnel(?string $developpement_personnel): self
    {
        $this->developpement_personnel = $developpement_personnel;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $recommandations = null;

    public function getRecommandations(): ?string
    {
        return $this->recommandations;
    }

    public function setRecommandations(?string $recommandations): self
    {
        $this->recommandations = $recommandations;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_suivi = null;

    public function getDate_suivi(): ?\DateTimeInterface
    {
        return $this->date_suivi;
    }

    public function setDate_suivi(\DateTimeInterface $date_suivi): self
    {
        $this->date_suivi = $date_suivi;
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

    public function getNiveauStress(): ?int
    {
        return $this->niveau_stress;
    }

    public function setNiveauStress(int $niveau_stress): static
    {
        $this->niveau_stress = $niveau_stress;

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

    public function getActivitePhysique(): ?string
    {
        return $this->activite_physique;
    }

    public function setActivitePhysique(?string $activite_physique): static
    {
        $this->activite_physique = $activite_physique;

        return $this;
    }

    public function getDeveloppementPersonnel(): ?string
    {
        return $this->developpement_personnel;
    }

    public function setDeveloppementPersonnel(?string $developpement_personnel): static
    {
        $this->developpement_personnel = $developpement_personnel;

        return $this;
    }

    public function getDateSuivi(): ?\DateTime
    {
        return $this->date_suivi;
    }

    public function setDateSuivi(\DateTime $date_suivi): static
    {
        $this->date_suivi = $date_suivi;

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
