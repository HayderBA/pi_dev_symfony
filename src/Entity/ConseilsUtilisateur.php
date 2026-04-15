<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ConseilsUtilisateurRepository;

#[ORM\Entity(repositoryClass: ConseilsUtilisateurRepository::class)]
#[ORM\Table(name: 'conseils_utilisateurs')]
class ConseilsUtilisateur
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'conseilsUtilisateurs')]
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

    #[ORM\ManyToOne(targetEntity: Conseil::class, inversedBy: 'conseilsUtilisateurs')]
    #[ORM\JoinColumn(name: 'conseil_id', referencedColumnName: 'id')]
    private ?Conseil $conseil = null;

    public function getConseil(): ?Conseil
    {
        return $this->conseil;
    }

    public function setConseil(?Conseil $conseil): self
    {
        $this->conseil = $conseil;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_attribution = null;

    public function getDate_attribution(): ?\DateTimeInterface
    {
        return $this->date_attribution;
    }

    public function setDate_attribution(?\DateTimeInterface $date_attribution): self
    {
        $this->date_attribution = $date_attribution;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $est_vu = null;

    public function getEst_vu(): ?int
    {
        return $this->est_vu;
    }

    public function setEst_vu(?int $est_vu): self
    {
        $this->est_vu = $est_vu;
        return $this;
    }

    public function getDateAttribution(): ?\DateTime
    {
        return $this->date_attribution;
    }

    public function setDateAttribution(?\DateTime $date_attribution): static
    {
        $this->date_attribution = $date_attribution;

        return $this;
    }

    public function getEstVu(): ?int
    {
        return $this->est_vu;
    }

    public function setEstVu(?int $est_vu): static
    {
        $this->est_vu = $est_vu;

        return $this;
    }

}
