<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'tests')]
class WellnessTest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'wellnessTests')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $utilisateur = null;

    #[ORM\Column(name: 'type_test', length: 50)]
    private ?string $typeTest = null;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 10)]
    private ?int $score = null;

    #[ORM\Column(name: 'date_test', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateTest = null;

    public function __construct()
    {
        $this->dateTest = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
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
}
