<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'programme_sportif')]
class ProgrammeSportif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'programmesSportifs')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\Range(min: 12, max: 100)]
    private ?int $age = null;

    #[ORM\Column(length: 10)]
    private ?string $genre = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $tailleCm = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $poidsKg = null;

    #[ORM\Column]
    private ?int $niveauStress = null;

    #[ORM\Column]
    private ?int $qualiteSommeil = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $dureeSommeilHeures = null;

    #[ORM\Column(length: 20)]
    private ?string $niveauActivite = null;

    #[ORM\Column(length: 30)]
    private ?string $objectif = null;

    #[ORM\Column(length: 80)]
    private ?string $activiteCible = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $imc = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $categorieImc = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $sourceImc = null;

    #[ORM\Column(nullable: true)]
    private ?int $besoinCalorique = null;

    #[ORM\Column(nullable: true)]
    private ?int $caloriesActivite = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $sourceCalories = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $intensite = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $typeProgramme = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resume = null;

    #[ORM\Column(type: Types::JSON)]
    private array $seances = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getTailleCm(): ?float
    {
        return $this->tailleCm;
    }

    public function setTailleCm(float $tailleCm): static
    {
        $this->tailleCm = $tailleCm;

        return $this;
    }

    public function getPoidsKg(): ?float
    {
        return $this->poidsKg;
    }

    public function setPoidsKg(float $poidsKg): static
    {
        $this->poidsKg = $poidsKg;

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

    public function getDureeSommeilHeures(): ?float
    {
        return $this->dureeSommeilHeures;
    }

    public function setDureeSommeilHeures(float $dureeSommeilHeures): static
    {
        $this->dureeSommeilHeures = $dureeSommeilHeures;

        return $this;
    }

    public function getNiveauActivite(): ?string
    {
        return $this->niveauActivite;
    }

    public function setNiveauActivite(string $niveauActivite): static
    {
        $this->niveauActivite = $niveauActivite;

        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(string $objectif): static
    {
        $this->objectif = $objectif;

        return $this;
    }

    public function getActiviteCible(): ?string
    {
        return $this->activiteCible;
    }

    public function setActiviteCible(string $activiteCible): static
    {
        $this->activiteCible = $activiteCible;

        return $this;
    }

    public function getImc(): ?float
    {
        return $this->imc;
    }

    public function setImc(?float $imc): static
    {
        $this->imc = $imc;

        return $this;
    }

    public function getCategorieImc(): ?string
    {
        return $this->categorieImc;
    }

    public function setCategorieImc(?string $categorieImc): static
    {
        $this->categorieImc = $categorieImc;

        return $this;
    }

    public function getSourceImc(): ?string
    {
        return $this->sourceImc;
    }

    public function setSourceImc(?string $sourceImc): static
    {
        $this->sourceImc = $sourceImc;

        return $this;
    }

    public function getBesoinCalorique(): ?int
    {
        return $this->besoinCalorique;
    }

    public function setBesoinCalorique(?int $besoinCalorique): static
    {
        $this->besoinCalorique = $besoinCalorique;

        return $this;
    }

    public function getCaloriesActivite(): ?int
    {
        return $this->caloriesActivite;
    }

    public function setCaloriesActivite(?int $caloriesActivite): static
    {
        $this->caloriesActivite = $caloriesActivite;

        return $this;
    }

    public function getSourceCalories(): ?string
    {
        return $this->sourceCalories;
    }

    public function setSourceCalories(?string $sourceCalories): static
    {
        $this->sourceCalories = $sourceCalories;

        return $this;
    }

    public function getIntensite(): ?string
    {
        return $this->intensite;
    }

    public function setIntensite(?string $intensite): static
    {
        $this->intensite = $intensite;

        return $this;
    }

    public function getTypeProgramme(): ?string
    {
        return $this->typeProgramme;
    }

    public function setTypeProgramme(?string $typeProgramme): static
    {
        $this->typeProgramme = $typeProgramme;

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(?string $resume): static
    {
        $this->resume = $resume;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSeances(): array
    {
        return $this->seances;
    }

    /**
     * @param array<int, array<string, mixed>> $seances
     */
    public function setSeances(array $seances): static
    {
        $this->seances = $seances;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
