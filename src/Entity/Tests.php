<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'tests', indexes: [new ORM\Index(name: 'utilisateur_id', columns: ['utilisateur_id'])])]
class Tests
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'type_test', length: 50)]
    #[Assert\NotBlank(message: 'Le type de test est obligatoire')]
    #[Assert\Length(max: 50, maxMessage: 'Maximum 50 caracteres')]
    private ?string $typeTest = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le score est obligatoire')]
    #[Assert\Range(
        min: 0,
        max: 10,
        notInRangeMessage: 'Le score doit etre entre {{ min }} et {{ max }}'
    )]
    private ?int $score = null;

    #[ORM\Column(name: 'date_test', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotNull(message: 'La date est obligatoire')]
    #[Assert\LessThanOrEqual('now', message: 'La date ne peut pas etre dans le futur')]
    private ?\DateTimeInterface $dateTest = null;

    #[ORM\ManyToOne(inversedBy: 'tests')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
    private ?Utilisateur $utilisateur = null;

    public function __construct()
    {
        $this->dateTest = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeTest(): ?string
    {
        return $this->typeTest;
    }

    public function setTypeTest(string $typeTest): static
    {
        $this->typeTest = $typeTest;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getDateTest(): ?\DateTimeInterface
    {
        return $this->dateTest;
    }

    public function setDateTest(?\DateTimeInterface $dateTest): static
    {
        $this->dateTest = $dateTest;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}
