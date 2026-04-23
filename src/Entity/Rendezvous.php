<?php

namespace App\Entity;

use App\Repository\RendezvousRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RendezvousRepository::class)]
#[ORM\Table(name: 'rendezvous')]
class Rendezvous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idRdv')]
    private ?int $idRdv = null;

    #[ORM\Column(name: 'dateRdv', type: 'date')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $dateRdv = null;

    #[ORM\Column(type: 'time')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $heure = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['en_attente', 'confirme', 'annule'])]
    private ?string $statut = 'en_attente';

    #[ORM\Column(name: 'typeCons', length: 30)]
    #[Assert\NotBlank]
    private ?string $typeCons = null;

    #[ORM\ManyToOne(targetEntity: Psychologue::class, inversedBy: 'rendezvous')]
    #[ORM\JoinColumn(name: 'idPsychologue', referencedColumnName: 'idPsychologue', nullable: false, onDelete: 'CASCADE')]
    private ?Psychologue $psychologue = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone_patient = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Email]
    private ?string $email_patient = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nom_patient = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $prenom_patient = null;

    #[ORM\Column(nullable: true)]
    private ?bool $rappel_envoye = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_rappel = null;

    #[ORM\Column(name: 'est_paye', nullable: true)]
    private ?bool $estPaye = false;

    #[ORM\Column(name: 'stripe_session_id', length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(name: 'tarif_consultation', nullable: true)]
    private ?float $tarifConsultation = null;

    public function getIdRdv(): ?int
    {
        return $this->idRdv;
    }

    public function getId(): ?int
    {
        return $this->idRdv;
    }

    public function getDateRdv(): ?\DateTimeInterface
    {
        return $this->dateRdv;
    }

    public function setDateRdv(\DateTimeInterface $dateRdv): static
    {
        $this->dateRdv = $dateRdv;

        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getTypeCons(): ?string
    {
        return $this->typeCons;
    }

    public function setTypeCons(string $typeCons): static
    {
        $this->typeCons = $typeCons;

        return $this;
    }

    public function getPsychologue(): ?Psychologue
    {
        return $this->psychologue;
    }

    public function setPsychologue(?Psychologue $psychologue): static
    {
        $this->psychologue = $psychologue;

        return $this;
    }

    public function getTelephonePatient(): ?string
    {
        return $this->telephone_patient;
    }

    public function setTelephonePatient(?string $telephonePatient): static
    {
        $this->telephone_patient = $telephonePatient;

        return $this;
    }

    public function getEmailPatient(): ?string
    {
        return $this->email_patient;
    }

    public function setEmailPatient(?string $emailPatient): static
    {
        $this->email_patient = $emailPatient;

        return $this;
    }

    public function getNomPatient(): ?string
    {
        return $this->nom_patient;
    }

    public function setNomPatient(?string $nomPatient): static
    {
        $this->nom_patient = $nomPatient;

        return $this;
    }

    public function getPrenomPatient(): ?string
    {
        return $this->prenom_patient;
    }

    public function setPrenomPatient(?string $prenomPatient): static
    {
        $this->prenom_patient = $prenomPatient;

        return $this;
    }

    public function isRappelEnvoye(): ?bool
    {
        return $this->rappel_envoye;
    }

    public function setRappelEnvoye(?bool $rappelEnvoye): static
    {
        $this->rappel_envoye = $rappelEnvoye;

        return $this;
    }

    public function getDateRappel(): ?\DateTimeInterface
    {
        return $this->date_rappel;
    }

    public function setDateRappel(?\DateTimeInterface $dateRappel): static
    {
        $this->date_rappel = $dateRappel;

        return $this;
    }

    public function isEstPaye(): ?bool
    {
        return $this->estPaye;
    }

    public function setEstPaye(?bool $estPaye): static
    {
        $this->estPaye = $estPaye;

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): static
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getTarifConsultation(): ?float
    {
        return $this->tarifConsultation;
    }

    public function setTarifConsultation(?float $tarifConsultation): static
    {
        $this->tarifConsultation = $tarifConsultation;

        return $this;
    }
}
