<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(name: 'second_name', length: 100)]
    private ?string $secondName = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(name: 'phone_number', nullable: true)]
    private ?int $phoneNumber = null;

    #[ORM\Column(name: 'birth_date', length: 20, nullable: true)]
    private ?string $birthDate = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(name: 'is_blocked')]
    private bool $isBlocked = false;

    #[ORM\Column(name: 'is_verified')]
    private bool $isVerified = false;

    #[ORM\Column(name: 'qr_token', length: 255, nullable: true)]
    private ?string $qrToken = null;

    #[ORM\Column(name: 'qr_expires_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $qrExpiresAt = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 8, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(name: 'fcm_token', length: 255, nullable: true)]
    private ?string $fcmToken = null;

    #[ORM\Column(name: 'telegram_chat_id', length: 255, nullable: true)]
    private ?string $telegramChatId = null;

    #[ORM\Column(name: 'google_authenticator_secret', length: 255, nullable: true)]
    private ?string $googleAuthenticatorSecret = null;

    #[ORM\Column(name: 'face_image', length: 255, nullable: true)]
    private ?string $faceImage = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class)]
    private Collection $sentMessages;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\ManyToMany(targetEntity: Conversation::class, mappedBy: 'participants')]
    private Collection $conversations;

    /**
     * @var Collection<int, SanteBienEtre>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SanteBienEtre::class)]
    private Collection $santeBienEtres;

    /**
     * @var Collection<int, SleepTracking>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SleepTracking::class)]
    private Collection $sleepTrackings;

    /**
     * @var Collection<int, Humeur>
     */
    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Humeur::class)]
    private Collection $humeurs;

    /**
     * @var Collection<int, WellnessTest>
     */
    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: WellnessTest::class)]
    private Collection $wellnessTests;

    /**
     * @var Collection<int, ProgrammeSportif>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ProgrammeSportif::class)]
    private Collection $programmesSportifs;

    /**
     * @var Collection<int, Conseil>
     */
    #[ORM\ManyToMany(targetEntity: Conseil::class, mappedBy: 'utilisateurs')]
    private Collection $conseils;

    public function __construct()
    {
        $this->sentMessages = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->santeBienEtres = new ArrayCollection();
        $this->sleepTrackings = new ArrayCollection();
        $this->humeurs = new ArrayCollection();
        $this->wellnessTests = new ArrayCollection();
        $this->programmesSportifs = new ArrayCollection();
        $this->conseils = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getSecondName(): ?string { return $this->secondName; }
    public function setSecondName(string $secondName): static { $this->secondName = $secondName; return $this; }
    public function getAge(): ?int { return $this->age; }
    public function setAge(?int $age): static { $this->age = $age; return $this; }
    public function getGender(): ?string { return $this->gender; }
    public function setGender(?string $gender): static { $this->gender = $gender; return $this; }
    public function getPhoneNumber(): ?int { return $this->phoneNumber; }
    public function setPhoneNumber(?int $phoneNumber): static { $this->phoneNumber = $phoneNumber; return $this; }
    public function getBirthDate(): ?string { return $this->birthDate; }
    public function setBirthDate(?string $birthDate): static { $this->birthDate = $birthDate; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getUserIdentifier(): string { return (string) $this->email; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }
    public function getRole(): ?string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }
    public function getRoles(): array
    {
        $role = strtolower((string) $this->role);
        $roles = ['ROLE_USER'];

        if ('patient' === $role) {
            $roles[] = 'ROLE_PATIENT';
        } elseif ('doctor' === $role || 'medecin' === $role) {
            $roles[] = 'ROLE_DOCTOR';
            $roles[] = 'ROLE_MEDECIN';
        } elseif ('admin' === $role) {
            $roles[] = 'ROLE_ADMIN';
        } elseif ('' !== $role) {
            $normalized = strtoupper($role);
            $roles[] = str_starts_with($normalized, 'ROLE_') ? $normalized : 'ROLE_' . $normalized;
        }

        return array_values(array_unique($roles));
    }
    public function eraseCredentials(): void {}
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): static { $this->phone = $phone; return $this; }
    public function isBlocked(): bool { return $this->isBlocked; }
    public function setIsBlocked(bool $isBlocked): static { $this->isBlocked = $isBlocked; return $this; }
    public function getIsBlocked(): bool { return $this->isBlocked; }
    public function isVerified(): bool { return $this->isVerified; }
    public function setIsVerified(bool $isVerified): static { $this->isVerified = $isVerified; return $this; }
    public function getIsVerified(): bool { return $this->isVerified; }
    public function getQrToken(): ?string { return $this->qrToken; }
    public function setQrToken(?string $qrToken): static { $this->qrToken = $qrToken; return $this; }
    public function getQrExpiresAt(): ?\DateTimeInterface { return $this->qrExpiresAt; }
    public function setQrExpiresAt(?\DateTimeInterface $qrExpiresAt): static { $this->qrExpiresAt = $qrExpiresAt; return $this; }
    public function getLatitude(): ?float { return $this->latitude !== null ? (float) $this->latitude : null; }
    public function setLatitude(?float $latitude): static { $this->latitude = $latitude !== null ? (string) $latitude : null; return $this; }
    public function getLongitude(): ?float { return $this->longitude !== null ? (float) $this->longitude : null; }
    public function setLongitude(?float $longitude): static { $this->longitude = $longitude !== null ? (string) $longitude : null; return $this; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): static { $this->adresse = $adresse; return $this; }
    public function getFcmToken(): ?string { return $this->fcmToken; }
    public function setFcmToken(?string $fcmToken): static { $this->fcmToken = $fcmToken; return $this; }
    public function getTelegramChatId(): ?string { return $this->telegramChatId; }
    public function setTelegramChatId(?string $telegramChatId): static { $this->telegramChatId = $telegramChatId; return $this; }
    public function getGoogleAuthenticatorSecret(): ?string
    {
        $secret = $this->googleAuthenticatorSecret !== null ? trim($this->googleAuthenticatorSecret) : null;

        return $secret === '' ? null : $secret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): static
    {
        $secret = $googleAuthenticatorSecret !== null ? trim($googleAuthenticatorSecret) : null;
        $this->googleAuthenticatorSecret = $secret === '' ? null : $secret;

        return $this;
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->isPatient() && null !== $this->getGoogleAuthenticatorSecret();
    }
    public function getGoogleAuthenticatorUsername(): string { return (string) $this->email; }
    public function getFaceImage(): ?string { return $this->faceImage; }
    public function setFaceImage(?string $faceImage): static { $this->faceImage = $faceImage; return $this; }
    public function getFullName(): string { return trim(($this->name ?? '') . ' ' . ($this->secondName ?? '')); }
    public function getInitials(): string
    {
        $parts = array_filter([
            $this->name ? strtoupper(substr($this->name, 0, 1)) : '',
            $this->secondName ? strtoupper(substr($this->secondName, 0, 1)) : '',
        ]);

        return implode('', $parts) ?: 'GM';
    }
    public function isAdmin(): bool { return 'admin' === strtolower((string) $this->role) || in_array('ROLE_ADMIN', $this->getRoles(), true); }
    public function isPatient(): bool { return 'patient' === strtolower((string) $this->role) || in_array('ROLE_PATIENT', $this->getRoles(), true); }
    public function isDoctor(): bool { return 'doctor' === strtolower((string) $this->role) || 'medecin' === strtolower((string) $this->role) || in_array('ROLE_DOCTOR', $this->getRoles(), true) || in_array('ROLE_MEDECIN', $this->getRoles(), true); }

    /**
     * @return Collection<int, Message>
     */
    public function getSentMessages(): Collection { return $this->sentMessages; }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection { return $this->conversations; }

    /**
     * @return Collection<int, SanteBienEtre>
     */
    public function getSanteBienEtres(): Collection { return $this->santeBienEtres; }

    /**
     * @return Collection<int, SleepTracking>
     */
    public function getSleepTrackings(): Collection { return $this->sleepTrackings; }

    /**
     * @return Collection<int, Humeur>
     */
    public function getHumeurs(): Collection { return $this->humeurs; }

    /**
     * @return Collection<int, WellnessTest>
     */
    public function getWellnessTests(): Collection { return $this->wellnessTests; }

    /**
     * @return Collection<int, ProgrammeSportif>
     */
    public function getProgrammesSportifs(): Collection { return $this->programmesSportifs; }

    /**
     * @return Collection<int, Conseil>
     */
    public function getConseils(): Collection { return $this->conseils; }
}
