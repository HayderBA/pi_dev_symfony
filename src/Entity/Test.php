<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\TestRepository;

#[ORM\Entity(repositoryClass: TestRepository::class)]
#[ORM\Table(name: 'tests')]
class Test
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tests')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id')]
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
    private ?string $type_test = null;

    public function getType_test(): ?string
    {
        return $this->type_test;
    }

    public function setType_test(string $type_test): self
    {
        $this->type_test = $type_test;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $score = null;

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_test = null;

    public function getDate_test(): ?\DateTimeInterface
    {
        return $this->date_test;
    }

    public function setDate_test(?\DateTimeInterface $date_test): self
    {
        $this->date_test = $date_test;
        return $this;
    }

    public function getTypeTest(): ?string
    {
        return $this->type_test;
    }

    public function setTypeTest(string $type_test): static
    {
        $this->type_test = $type_test;

        return $this;
    }

    public function getDateTest(): ?\DateTime
    {
        return $this->date_test;
    }

    public function setDateTest(?\DateTime $date_test): static
    {
        $this->date_test = $date_test;

        return $this;
    }

}
