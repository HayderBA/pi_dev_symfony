<?php

namespace App\Entity;

use App\Repository\PsychologueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PsychologueRepository::class)]
#[ORM\Table(name: 'psychologue')]
class Psychologue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idPsychologue')]
    private ?int $idPsychologue = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $prenom = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    private ?string $specialite = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    private ?string $diplome = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?int $experience = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?float $tarif = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    private ?string $telephone = null;

    #[ORM\ManyToOne(targetEntity: Cabinet::class, inversedBy: 'psychologues')]
    #[ORM\JoinColumn(name: 'idCabinet', referencedColumnName: 'idCabinet', nullable: true, onDelete: 'SET NULL')]
    private ?Cabinet $cabinet = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * @var Collection<int, Rendezvous>
     */
    #[ORM\OneToMany(mappedBy: 'psychologue', targetEntity: Rendezvous::class)]
    private Collection $rendezvous;

    public function __construct()
    {
        $this->rendezvous = new ArrayCollection();
    }

    public function getIdPsychologue(): ?int
    {
        return $this->idPsychologue;
    }

    public function getId(): ?int
    {
        return $this->idPsychologue;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getDiplome(): ?string
    {
        return $this->diplome;
    }

    public function setDiplome(string $diplome): static
    {
        $this->diplome = $diplome;

        return $this;
    }

    public function getExperience(): ?int
    {
        return $this->experience;
    }

    public function setExperience(int $experience): static
    {
        $this->experience = $experience;

        return $this;
    }

    public function getTarif(): ?float
    {
        return $this->tarif;
    }

    public function setTarif(float $tarif): static
    {
        $this->tarif = $tarif;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getCabinet(): ?Cabinet
    {
        return $this->cabinet;
    }

    public function setCabinet(?Cabinet $cabinet): static
    {
        $this->cabinet = $cabinet;

        return $this;
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

    public function getDisplayName(): string
    {
        $first = $this->prenom ?: ($this->user?->getName() ?? '');
        $last = $this->nom ?: ($this->user?->getSecondName() ?? '');

        return trim($first . ' ' . $last) ?: 'Psychologue #' . $this->idPsychologue;
    }

    public function syncWithUser(): void
    {
        if (!$this->user) {
            return;
        }

        $this->nom = $this->user->getSecondName() ?: $this->nom;
        $this->prenom = $this->user->getName() ?: $this->prenom;
        $this->email = $this->user->getEmail() ?: $this->email;
        $this->telephone = $this->user->getPhone() ?: ((string) ($this->user->getPhoneNumber() ?? $this->telephone));
    }
}
