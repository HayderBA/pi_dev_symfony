<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "userId")]
    private ?int $userId = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(name: "ressourceId", referencedColumnName: "id", nullable: false)]
    private ?Ressource $ressource = null;

    #[ORM\Column]
    private ?int $note = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[AppAssert\BadWords]
    private ?string $commentaire = null;

    #[ORM\Column(name: "dateEvaluation", type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateEvaluation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getRessource(): ?Ressource
    {
        return $this->ressource;
    }

    public function setRessource(?Ressource $ressource): static
    {
        $this->ressource = $ressource;
        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getDateEvaluation(): ?\DateTimeInterface
    {
        return $this->dateEvaluation;
    }

    public function setDateEvaluation(?\DateTimeInterface $dateEvaluation): static
    {
        $this->dateEvaluation = $dateEvaluation;
        return $this;
    }
}
