<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'utilisateur', uniqueConstraints: [new ORM\UniqueConstraint(name: 'email', columns: ['email'])])]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SanteBienEtre::class)]
    private Collection $santeBienEtres;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SleepTracking::class)]
    private Collection $sleepTrackings;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Humeurs::class)]
    private Collection $humeurs;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Tests::class)]
    private Collection $tests;

    #[ORM\ManyToMany(targetEntity: Conseils::class, mappedBy: 'utilisateurs')]
    private Collection $conseils;

    public function __construct()
    {
        $this->santeBienEtres = new ArrayCollection();
        $this->sleepTrackings = new ArrayCollection();
        $this->humeurs = new ArrayCollection();
        $this->tests = new ArrayCollection();
        $this->conseils = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom ?: ($this->email ?: (string) $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, SanteBienEtre>
     */
    public function getSanteBienEtres(): Collection
    {
        return $this->santeBienEtres;
    }

    /**
     * @return Collection<int, SleepTracking>
     */
    public function getSleepTrackings(): Collection
    {
        return $this->sleepTrackings;
    }

    /**
     * @return Collection<int, Humeurs>
     */
    public function getHumeurs(): Collection
    {
        return $this->humeurs;
    }

    /**
     * @return Collection<int, Tests>
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    /**
     * @return Collection<int, Conseils>
     */
    public function getConseils(): Collection
    {
        return $this->conseils;
    }

    public function addConseil(Conseils $conseil): static
    {
        if (! $this->conseils->contains($conseil)) {
            $this->conseils->add($conseil);
        }

        return $this;
    }

    public function removeConseil(Conseils $conseil): static
    {
        $this->conseils->removeElement($conseil);

        return $this;
    }
}
