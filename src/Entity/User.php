<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "users")]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $secondName = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(name: "password", type: "string", length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    // ========== GÉOLOCALISATION ==========
    #[ORM\Column(type: 'decimal', precision: 10, scale: 8, nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresse = null;

    // ========== FIREBASE TOKEN ==========
    #[ORM\Column(name: "fcm_token", type: "string", length: 255, nullable: true)]
    private ?string $fcmToken = null;

    // ========== RELATIONS ==========
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender')]
    private Collection $sentMessages;

    #[ORM\ManyToMany(targetEntity: Conversation::class, mappedBy: 'participants')]
    private Collection $conversations;

    public function __construct()
    {
        $this->sentMessages = new ArrayCollection();
        $this->conversations = new ArrayCollection();
    }

    // ========== GETTERS/SETTERS ==========
    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getSecondName(): ?string { return $this->secondName; }
    public function setSecondName(string $secondName): static { $this->secondName = $secondName; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }
    public function getRole(): ?string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }

    // ========== GÉOLOCALISATION ==========
    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): static { $this->latitude = $latitude; return $this; }
    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): static { $this->longitude = $longitude; return $this; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): static { $this->adresse = $adresse; return $this; }

    // ========== FIREBASE TOKEN ==========
    public function getFcmToken(): ?string { return $this->fcmToken; }
    public function setFcmToken(?string $fcmToken): static { $this->fcmToken = $fcmToken; return $this; }

    // ========== RELATIONS ==========
    public function getSentMessages(): Collection { return $this->sentMessages; }
    public function getConversations(): Collection { return $this->conversations; }
    public function addConversation(Conversation $conversation): static {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->addParticipant($this);
        }
        return $this;
    }

    // ========== UTILITAIRES ==========
    public function getFullName(): string { return $this->name . ' ' . $this->secondName; }
}