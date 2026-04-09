<?php

namespace App\Entity;

use App\Repository\CabinetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CabinetRepository::class)]
#[ORM\Table(name: 'cabinet')]
class Cabinet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idCabinet', type: 'integer')]
    private ?int $idCabinet = null;

    public function getIdCabinet(): ?int
    {
        return $this->idCabinet;
    }

    public function setIdCabinet(int $idCabinet): self
    {
        $this->idCabinet = $idCabinet;

        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le nom du cabinet est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $nomcabinet = null;

    public function getNomcabinet(): ?string
    {
        return $this->nomcabinet;
    }

    public function setNomcabinet(string $nomcabinet): self
    {
        $this->nomcabinet = $nomcabinet;

        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'L’adresse est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'L’adresse ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $adresse = null;

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'La ville est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'La ville doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'La ville ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $ville = null;

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull(message: 'Le numéro de téléphone est obligatoire.')]
    #[Assert\Positive(message: 'Le numéro de téléphone doit être un nombre positif.')]
    #[Assert\LessThan(value: 10000000000000, message: 'Le numéro de téléphone est trop long.')]
    private ?int $telephone = null;

    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    public function setTelephone(int $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'L’email est obligatoire.')]
    #[Assert\Email(message: 'Veuillez saisir une adresse email valide (ex. contact@exemple.com).')]
    #[Assert\Length(max: 180, maxMessage: 'L’email ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(max: 2000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    #[Assert\Choice(
        choices: ['actif', 'inactif'],
        message: 'Le statut doit être « actif » ou « inactif ».'
    )]
    private ?string $status = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
