<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'sante_bien_etre')]
class SanteBienEtre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'santeBienEtres')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $humeur = null;

    #[ORM\Column(name: 'niveau_stress')]
    #[Assert\Range(min: 1, max: 10)]
    private ?int $niveauStress = null;

    #[ORM\Column(name: 'qualite_sommeil')]
    #[Assert\Range(min: 1, max: 10)]
    private ?int $qualiteSommeil = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nutrition = null;

    #[ORM\Column(name: 'activite_physique', length: 255, nullable: true)]
    private ?string $activitePhysique = null;

    #[ORM\Column(name: 'developpement_personnel', length: 500, nullable: true)]
    private ?string $developpementPersonnel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $recommandations = null;

    #[ORM\Column(name: 'date_suivi', type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateSuivi = null;

    #[ORM\Column(name: 'date_creation', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->dateSuivi = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getHumeur(): ?string
    {
        return $this->humeur;
    }

    public function setHumeur(string $humeur): static
    {
        $this->humeur = $humeur;

        return $this;
    }

    public function getNiveauStress(): ?int
    {
        return $this->niveauStress;
    }

    public function setNiveauStress(int $niveauStress): static
    {
        $this->niveauStress = $niveauStress;

        return $this;
    }

    public function getQualiteSommeil(): ?int
    {
        return $this->qualiteSommeil;
    }

    public function setQualiteSommeil(int $qualiteSommeil): static
    {
        $this->qualiteSommeil = $qualiteSommeil;

        return $this;
    }

    public function getNutrition(): ?string
    {
        return $this->nutrition;
    }

    public function setNutrition(?string $nutrition): static
    {
        $this->nutrition = $nutrition;

        return $this;
    }

    public function getActivitePhysique(): ?string
    {
        return $this->activitePhysique;
    }

    public function setActivitePhysique(?string $activitePhysique): static
    {
        $this->activitePhysique = $activitePhysique;

        return $this;
    }

    public function getDeveloppementPersonnel(): ?string
    {
        return $this->developpementPersonnel;
    }

    public function setDeveloppementPersonnel(?string $developpementPersonnel): static
    {
        $this->developpementPersonnel = $developpementPersonnel;

        return $this;
    }

    public function getRecommandations(): ?string
    {
        return $this->recommandations;
    }

    public function setRecommandations(?string $recommandations): static
    {
        $this->recommandations = $recommandations;

        return $this;
    }

    public function getDateSuivi(): ?\DateTimeInterface
    {
        return $this->dateSuivi;
    }

    public function setDateSuivi(\DateTimeInterface $dateSuivi): static
    {
        $this->dateSuivi = $dateSuivi;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }
}
