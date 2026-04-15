<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\RendezvouRepository;

#[ORM\Entity(repositoryClass: RendezvouRepository::class)]
#[ORM\Table(name: 'rendezvous')]
class Rendezvou
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idRdv = null;

    public function getIdRdv(): ?int
    {
        return $this->idRdv;
    }

    public function setIdRdv(int $idRdv): self
    {
        $this->idRdv = $idRdv;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $dateRdv = null;

    public function getDateRdv(): ?\DateTimeInterface
    {
        return $this->dateRdv;
    }

    public function setDateRdv(\DateTimeInterface $dateRdv): self
    {
        $this->dateRdv = $dateRdv;
        return $this;
    }

    #[ORM\Column(type: 'time', nullable: false)]
    private ?string $heure = null;

    public function getHeure(): ?string
    {
        return $this->heure;
    }

    public function setHeure(string $heure): self
    {
        $this->heure = $heure;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $typeCons = null;

    public function getTypeCons(): ?string
    {
        return $this->typeCons;
    }

    public function setTypeCons(string $typeCons): self
    {
        $this->typeCons = $typeCons;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Psychologue::class, inversedBy: 'rendezvous')]
    #[ORM\JoinColumn(name: 'idPsychologue', referencedColumnName: 'idPsychologue')]
    private ?Psychologue $psychologue = null;

    public function getPsychologue(): ?Psychologue
    {
        return $this->psychologue;
    }

    public function setPsychologue(?Psychologue $psychologue): self
    {
        $this->psychologue = $psychologue;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $telephone_patient = null;

    public function getTelephone_patient(): ?string
    {
        return $this->telephone_patient;
    }

    public function setTelephone_patient(?string $telephone_patient): self
    {
        $this->telephone_patient = $telephone_patient;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $email_patient = null;

    public function getEmail_patient(): ?string
    {
        return $this->email_patient;
    }

    public function setEmail_patient(?string $email_patient): self
    {
        $this->email_patient = $email_patient;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nom_patient = null;

    public function getNom_patient(): ?string
    {
        return $this->nom_patient;
    }

    public function setNom_patient(?string $nom_patient): self
    {
        $this->nom_patient = $nom_patient;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $prenom_patient = null;

    public function getPrenom_patient(): ?string
    {
        return $this->prenom_patient;
    }

    public function setPrenom_patient(?string $prenom_patient): self
    {
        $this->prenom_patient = $prenom_patient;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rappel_envoye = null;

    public function getRappel_envoye(): ?int
    {
        return $this->rappel_envoye;
    }

    public function setRappel_envoye(?int $rappel_envoye): self
    {
        $this->rappel_envoye = $rappel_envoye;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_rappel = null;

    public function getDate_rappel(): ?\DateTimeInterface
    {
        return $this->date_rappel;
    }

    public function setDate_rappel(?\DateTimeInterface $date_rappel): self
    {
        $this->date_rappel = $date_rappel;
        return $this;
    }

    public function getTelephonePatient(): ?string
    {
        return $this->telephone_patient;
    }

    public function setTelephonePatient(?string $telephone_patient): static
    {
        $this->telephone_patient = $telephone_patient;

        return $this;
    }

    public function getEmailPatient(): ?string
    {
        return $this->email_patient;
    }

    public function setEmailPatient(?string $email_patient): static
    {
        $this->email_patient = $email_patient;

        return $this;
    }

    public function getNomPatient(): ?string
    {
        return $this->nom_patient;
    }

    public function setNomPatient(?string $nom_patient): static
    {
        $this->nom_patient = $nom_patient;

        return $this;
    }

    public function getPrenomPatient(): ?string
    {
        return $this->prenom_patient;
    }

    public function setPrenomPatient(?string $prenom_patient): static
    {
        $this->prenom_patient = $prenom_patient;

        return $this;
    }

    public function getRappelEnvoye(): ?int
    {
        return $this->rappel_envoye;
    }

    public function setRappelEnvoye(?int $rappel_envoye): static
    {
        $this->rappel_envoye = $rappel_envoye;

        return $this;
    }

    public function getDateRappel(): ?\DateTime
    {
        return $this->date_rappel;
    }

    public function setDateRappel(?\DateTime $date_rappel): static
    {
        $this->date_rappel = $date_rappel;

        return $this;
    }

}
