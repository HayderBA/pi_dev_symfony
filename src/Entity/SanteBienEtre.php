<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'sante_bien_etre', indexes: [
    new ORM\Index(name: 'idx_sante_user_date', columns: ['user_id', 'date_suivi']),
    new ORM\Index(name: 'fk_sante_utilisateur', columns: ['user_id']),
    new ORM\Index(name: 'idx_sante_user_id', columns: ['user_id']),
    new ORM\Index(name: 'idx_sante_date_suivi', columns: ['date_suivi']),
])]
class SanteBienEtre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Humeur is required.')]
    private ?string $humeur = null;

    #[ORM\Column(name: 'niveau_stress')]
    #[Assert\Range(
        min: 1,
        max: 10,
        notInRangeMessage: 'Stress level must be between {{ min }} and {{ max }}.'
    )]
    private ?int $niveauStress = null;

    #[ORM\Column(name: 'qualite_sommeil')]
    #[Assert\Range(
        min: 1,
        max: 10,
        notInRangeMessage: 'Sleep quality must be between {{ min }} and {{ max }}.'
    )]
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
    #[Assert\NotBlank(message: 'Follow-up date is required.')]
    private ?\DateTimeInterface $dateSuivi = null;

    #[ORM\Column(name: 'date_creation', type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'santeBienEtres')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $user = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    public function setUser(?Utilisateur $user): static
    {
        $this->user = $user;

        return $this;
    }
}
