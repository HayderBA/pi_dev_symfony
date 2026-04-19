<?php

namespace App\Entity;

use App\Repository\RendezvouRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RendezvouRepository::class)]
#[ORM\Table(name: 'rendezvous')]
class Rendezvou
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idRdv', type: 'integer')]
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

    #[ORM\Column(name: 'dateRdv', type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'La date du rendez-vous est obligatoire.')]
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
    #[Assert\NotNull(message: 'L’heure du rendez-vous est obligatoire.')]
    private ?\DateTimeInterface $heure = null;

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): self
    {
        $this->heure = $heure;

        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    #[Assert\Choice(
        choices: ['en_attente', 'confirme', 'annule'],
        message: 'Le statut doit être « en attente », « confirme » ou « annule ».'
    )]
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

    #[ORM\Column(name: 'typeCons', type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le type de consultation est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le type ne peut pas dépasser {{ limit }} caractères.')]
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

    #[ORM\Column(name: 'idPsychologue', type: 'integer', nullable: true)]
    #[Assert\NotNull(message: 'Un psychologue doit être associé au rendez-vous.')]
    #[Assert\Positive(message: 'L’identifiant du psychologue doit être un nombre positif.')]
    private ?int $idPsychologue = null;

    public function getIdPsychologue(): ?int
    {
        return $this->idPsychologue;
    }

    public function setIdPsychologue(?int $idPsychologue): self
    {
        $this->idPsychologue = $idPsychologue;

        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'Le téléphone du patient est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[0-9+\s.-]{8,20}$/',
        message: 'Le numéro doit contenir entre 8 et 20 chiffres (caractères +, espaces et tirets autorisés).'
    )]
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
    #[Assert\NotBlank(message: 'L’email du patient est obligatoire.')]
    #[Assert\Email(message: 'Veuillez saisir une adresse email valide.')]
    #[Assert\Length(max: 180)]
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
    #[Assert\NotBlank(message: 'Le nom du patient est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 80,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
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
    #[Assert\NotBlank(message: 'Le prénom du patient est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 80,
        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.'
    )]
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

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $rappel_envoye = null;

    public function isRappel_envoye(): ?bool
    {
        return $this->rappel_envoye;
    }

    public function setRappel_envoye(?bool $rappel_envoye): self
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

    public function isRappelEnvoye(): ?bool
    {
        return $this->rappel_envoye;
    }

    public function setRappelEnvoye(?bool $rappel_envoye): static
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

    // ========== AJOUTS POUR LE PAIEMENT ==========

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $estPaye = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $tarifConsultation = null;

    // Getters et setters pour le paiement
    public function isEstPaye(): ?bool 
    { 
        return $this->estPaye; 
    }

    public function setEstPaye(?bool $estPaye): self 
    { 
        $this->estPaye = $estPaye; 
        return $this; 
    }

    public function getStripeSessionId(): ?string 
    { 
        return $this->stripeSessionId; 
    }

    public function setStripeSessionId(?string $stripeSessionId): self 
    { 
        $this->stripeSessionId = $stripeSessionId; 
        return $this; 
    }

    public function getMontant(): ?float 
    { 
        return $this->montant; 
    }

    public function setMontant(?float $montant): self 
    { 
        $this->montant = $montant; 
        return $this; 
    }

    public function getTarifConsultation(): ?float 
    { 
        return $this->tarifConsultation; 
    }

    public function setTarifConsultation(?float $tarifConsultation): self 
    { 
        $this->tarifConsultation = $tarifConsultation; 
        return $this; 
    }
}