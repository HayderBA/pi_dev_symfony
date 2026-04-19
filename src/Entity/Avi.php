<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\AviRepository;

#[ORM\Entity(repositoryClass: AviRepository::class)]
#[ORM\Table(name: 'avis')]
class Avi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idAvis = null;

    public function getIdAvis(): ?int
    {
        return $this->idAvis;
    }

    public function setIdAvis(int $idAvis): self
    {
        $this->idAvis = $idAvis;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $idReservation = null;

    public function getIdReservation(): ?int
    {
        return $this->idReservation;
    }

    public function setIdReservation(int $idReservation): self
    {
        $this->idReservation = $idReservation;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $utilisateur_id = null;

    public function getUtilisateur_id(): ?int
    {
        return $this->utilisateur_id;
    }

    public function setUtilisateur_id(int $utilisateur_id): self
    {
        $this->utilisateur_id = $utilisateur_id;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $note = null;

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_avis = null;

    public function getDate_avis(): ?\DateTimeInterface
    {
        return $this->date_avis;
    }

    public function setDate_avis(\DateTimeInterface $date_avis): self
    {
        $this->date_avis = $date_avis;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $reservation_id = null;

    public function getReservation_id(): ?int
    {
        return $this->reservation_id;
    }

    public function setReservation_id(?int $reservation_id): self
    {
        $this->reservation_id = $reservation_id;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $sentiment_score = null;

    public function getSentiment_score(): ?float
    {
        return $this->sentiment_score;
    }

    public function setSentiment_score(?float $sentiment_score): self
    {
        $this->sentiment_score = $sentiment_score;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $authenticity_score = null;

    public function getAuthenticity_score(): ?float
    {
        return $this->authenticity_score;
    }

    public function setAuthenticity_score(?float $authenticity_score): self
    {
        $this->authenticity_score = $authenticity_score;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $review_category = null;

    public function getReview_category(): ?string
    {
        return $this->review_category;
    }

    public function setReview_category(?string $review_category): self
    {
        $this->review_category = $review_category;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $is_verified = null;

    public function getIs_verified(): ?int
    {
        return $this->is_verified;
    }

    public function setIs_verified(?int $is_verified): self
    {
        $this->is_verified = $is_verified;
        return $this;
    }

    public function getUtilisateurId(): ?int
    {
        return $this->utilisateur_id;
    }

    public function setUtilisateurId(int $utilisateur_id): static
    {
        $this->utilisateur_id = $utilisateur_id;

        return $this;
    }

    public function getDateAvis(): ?\DateTime
    {
        return $this->date_avis;
    }

    public function setDateAvis(\DateTime $date_avis): static
    {
        $this->date_avis = $date_avis;

        return $this;
    }

    public function getReservationId(): ?int
    {
        return $this->reservation_id;
    }

    public function setReservationId(?int $reservation_id): static
    {
        $this->reservation_id = $reservation_id;

        return $this;
    }

    public function getSentimentScore(): ?string
    {
        return $this->sentiment_score;
    }

    public function setSentimentScore(?string $sentiment_score): static
    {
        $this->sentiment_score = $sentiment_score;

        return $this;
    }

    public function getAuthenticityScore(): ?string
    {
        return $this->authenticity_score;
    }

    public function setAuthenticityScore(?string $authenticity_score): static
    {
        $this->authenticity_score = $authenticity_score;

        return $this;
    }

    public function getReviewCategory(): ?string
    {
        return $this->review_category;
    }

    public function setReviewCategory(?string $review_category): static
    {
        $this->review_category = $review_category;

        return $this;
    }

    public function getIsVerified(): ?int
    {
        return $this->is_verified;
    }

    public function setIsVerified(?int $is_verified): static
    {
        $this->is_verified = $is_verified;

        return $this;
    }

}
