<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
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

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(min: 2, minMessage: "Minimum 2 caractères")]
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    private ?string $second_name = null;

    public function getSecond_name(): ?string
    {
        return $this->second_name;
    }

    public function setSecond_name(string $second_name): self
    {
        $this->second_name = $second_name;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]#[Assert\NotBlank(message: "L'âge est obligatoire")]
    #[Assert\Range(
        min: 1,
        max: 120,
        notInRangeMessage: "Âge invalide"
    )]
    private ?int $age = null;

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Genre obligatoire")]
    #[Assert\Choice(
        choices: ["homme", "femme"],
        message: "Choix invalide"
    )]
    private ?string $gender = null;

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank(message: "Numéro obligatoire")]
    #[Assert\Regex(
        pattern: "/^[0-9]{8}$/",
        message: "Numéro invalide (8 chiffres)"
    )]
    private ?int $phone_number = null;

    public function getPhone_number(): ?int
    {
        return $this->phone_number;
    }

    public function setPhone_number(?int $phone_number): self
    {
        $this->phone_number = $phone_number;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Date de naissance obligatoire")]
    #[Assert\Regex(
        pattern: "/^\d{4}-\d{2}-\d{2}$/",
        message: "Format date invalide (YYYY-MM-DD)"
    )]
    private ?string $birth_date = null;

    public function getBirth_date(): ?string
    {
        return $this->birth_date;
    }

    public function setBirth_date(?string $birth_date): self
    {
        $this->birth_date = $birth_date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Email est obligatoire")]
    #[Assert\Regex(
    pattern: "/^[^@\s]+@[^@\s]+\.[^@\s]+$/",
    message: "Email doit contenir @ et ."
    )]
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
    #[Assert\NotBlank(message: "Mot de passe obligatoire")]
    #[Assert\Length(
        min: 6,
        minMessage: "Au moins 6 caractères"
    )]
    private ?string $password = null;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $role = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $is_blocked = null;

    public function getIs_blocked(): ?int
    {
        return $this->is_blocked;
    }

    public function setIs_blocked(?int $is_blocked): self
    {
        $this->is_blocked = $is_blocked;
        return $this;
    }


    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $dtype = null;

    public function getDtype(): ?string
    {
        return $this->dtype;
    }

    public function setDtype(string $dtype): self
    {
        $this->dtype = $dtype;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Admin::class, mappedBy: 'user')]
    private Collection $admins;

    /**
     * @return Collection<int, Admin>
     */
    public function getAdmins(): Collection
    {
        if (!$this->admins instanceof Collection) {
            $this->admins = new ArrayCollection();
        }
        return $this->admins;
    }

    public function addAdmin(Admin $admin): self
    {
        if (!$this->getAdmins()->contains($admin)) {
            $this->getAdmins()->add($admin);
        }
        return $this;
    }

    public function removeAdmin(Admin $admin): self
    {
        $this->getAdmins()->removeElement($admin);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: AlertesUrgence::class, mappedBy: 'user')]
    private Collection $alertesUrgences;

    /**
     * @return Collection<int, AlertesUrgence>
     */
    public function getAlertesUrgences(): Collection
    {
        if (!$this->alertesUrgences instanceof Collection) {
            $this->alertesUrgences = new ArrayCollection();
        }
        return $this->alertesUrgences;
    }

    public function addAlertesUrgence(AlertesUrgence $alertesUrgence): self
    {
        if (!$this->getAlertesUrgences()->contains($alertesUrgence)) {
            $this->getAlertesUrgences()->add($alertesUrgence);
        }
        return $this;
    }

    public function removeAlertesUrgence(AlertesUrgence $alertesUrgence): self
    {
        $this->getAlertesUrgences()->removeElement($alertesUrgence);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ConseilsUtilisateur::class, mappedBy: 'user')]
    private Collection $conseilsUtilisateurs;

    /**
     * @return Collection<int, ConseilsUtilisateur>
     */
    public function getConseilsUtilisateurs(): Collection
    {
        if (!$this->conseilsUtilisateurs instanceof Collection) {
            $this->conseilsUtilisateurs = new ArrayCollection();
        }
        return $this->conseilsUtilisateurs;
    }

    public function addConseilsUtilisateur(ConseilsUtilisateur $conseilsUtilisateur): self
    {
        if (!$this->getConseilsUtilisateurs()->contains($conseilsUtilisateur)) {
            $this->getConseilsUtilisateurs()->add($conseilsUtilisateur);
        }
        return $this;
    }

    public function removeConseilsUtilisateur(ConseilsUtilisateur $conseilsUtilisateur): self
    {
        $this->getConseilsUtilisateurs()->removeElement($conseilsUtilisateur);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Doctor::class, mappedBy: 'user')]
    private Collection $doctors;

    /**
     * @return Collection<int, Doctor>
     */
    public function getDoctors(): Collection
    {
        if (!$this->doctors instanceof Collection) {
            $this->doctors = new ArrayCollection();
        }
        return $this->doctors;
    }

    public function addDoctor(Doctor $doctor): self
    {
        if (!$this->getDoctors()->contains($doctor)) {
            $this->getDoctors()->add($doctor);
        }
        return $this;
    }

    public function removeDoctor(Doctor $doctor): self
    {
        $this->getDoctors()->removeElement($doctor);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'user')]
    private Collection $evaluations;

    /**
     * @return Collection<int, Evaluation>
     */
    public function getEvaluations(): Collection
    {
        if (!$this->evaluations instanceof Collection) {
            $this->evaluations = new ArrayCollection();
        }
        return $this->evaluations;
    }

    public function addEvaluation(Evaluation $evaluation): self
    {
        if (!$this->getEvaluations()->contains($evaluation)) {
            $this->getEvaluations()->add($evaluation);
        }
        return $this;
    }

    public function removeEvaluation(Evaluation $evaluation): self
    {
        $this->getEvaluations()->removeElement($evaluation);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ForumPost::class, mappedBy: 'user')]
    private Collection $forumPosts;

    /**
     * @return Collection<int, ForumPost>
     */
    public function getForumPosts(): Collection
    {
        if (!$this->forumPosts instanceof Collection) {
            $this->forumPosts = new ArrayCollection();
        }
        return $this->forumPosts;
    }

    public function addForumPost(ForumPost $forumPost): self
    {
        if (!$this->getForumPosts()->contains($forumPost)) {
            $this->getForumPosts()->add($forumPost);
        }
        return $this;
    }

    public function removeForumPost(ForumPost $forumPost): self
    {
        $this->getForumPosts()->removeElement($forumPost);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Humeur::class, mappedBy: 'user')]
    private Collection $humeurs;

    /**
     * @return Collection<int, Humeur>
     */
    public function getHumeurs(): Collection
    {
        if (!$this->humeurs instanceof Collection) {
            $this->humeurs = new ArrayCollection();
        }
        return $this->humeurs;
    }

    public function addHumeur(Humeur $humeur): self
    {
        if (!$this->getHumeurs()->contains($humeur)) {
            $this->getHumeurs()->add($humeur);
        }
        return $this;
    }

    public function removeHumeur(Humeur $humeur): self
    {
        $this->getHumeurs()->removeElement($humeur);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Patient::class, mappedBy: 'user')]
    private Collection $patients;

    /**
     * @return Collection<int, Patient>
     */
    public function getPatients(): Collection
    {
        if (!$this->patients instanceof Collection) {
            $this->patients = new ArrayCollection();
        }
        return $this->patients;
    }

    public function addPatient(Patient $patient): self
    {
        if (!$this->getPatients()->contains($patient)) {
            $this->getPatients()->add($patient);
        }
        return $this;
    }

    public function removePatient(Patient $patient): self
    {
        $this->getPatients()->removeElement($patient);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'user')]
    private Collection $reponses;

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        if (!$this->reponses instanceof Collection) {
            $this->reponses = new ArrayCollection();
        }
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): self
    {
        if (!$this->getReponses()->contains($reponse)) {
            $this->getReponses()->add($reponse);
        }
        return $this;
    }

    public function removeReponse(Reponse $reponse): self
    {
        $this->getReponses()->removeElement($reponse);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user')]
    private Collection $reservations;

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        if (!$this->reservations instanceof Collection) {
            $this->reservations = new ArrayCollection();
        }
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->getReservations()->contains($reservation)) {
            $this->getReservations()->add($reservation);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        $this->getReservations()->removeElement($reservation);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: SanteBienEtre::class, mappedBy: 'user')]
    private Collection $santeBienEtres;

    /**
     * @return Collection<int, SanteBienEtre>
     */
    public function getSanteBienEtres(): Collection
    {
        if (!$this->santeBienEtres instanceof Collection) {
            $this->santeBienEtres = new ArrayCollection();
        }
        return $this->santeBienEtres;
    }

    public function addSanteBienEtre(SanteBienEtre $santeBienEtre): self
    {
        if (!$this->getSanteBienEtres()->contains($santeBienEtre)) {
            $this->getSanteBienEtres()->add($santeBienEtre);
        }
        return $this;
    }

    public function removeSanteBienEtre(SanteBienEtre $santeBienEtre): self
    {
        $this->getSanteBienEtres()->removeElement($santeBienEtre);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: SleepTracking::class, mappedBy: 'user')]
    private Collection $sleepTrackings;

    /**
     * @return Collection<int, SleepTracking>
     */
    public function getSleepTrackings(): Collection
    {
        if (!$this->sleepTrackings instanceof Collection) {
            $this->sleepTrackings = new ArrayCollection();
        }
        return $this->sleepTrackings;
    }

    public function addSleepTracking(SleepTracking $sleepTracking): self
    {
        if (!$this->getSleepTrackings()->contains($sleepTracking)) {
            $this->getSleepTrackings()->add($sleepTracking);
        }
        return $this;
    }

    public function removeSleepTracking(SleepTracking $sleepTracking): self
    {
        $this->getSleepTrackings()->removeElement($sleepTracking);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Test::class, mappedBy: 'user')]
    private Collection $tests;

    /**
     * @return Collection<int, Test>
     */
    public function getTests(): Collection
    {
        if (!$this->tests instanceof Collection) {
            $this->tests = new ArrayCollection();
        }
        return $this->tests;
    }

    public function addTest(Test $test): self
    {
        if (!$this->getTests()->contains($test)) {
            $this->getTests()->add($test);
        }
        return $this;
    }

    public function removeTest(Test $test): self
    {
        $this->getTests()->removeElement($test);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Ressource::class, inversedBy: 'users')]
    #[ORM\JoinTable(
        name: 'favori',
        joinColumns: [
            new ORM\JoinColumn(name: 'userId', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'ressourceId', referencedColumnName: 'id')
        ]
    )]
    private Collection $ressources;

    public function __construct()
    {
        $this->admins = new ArrayCollection();
        $this->alertesUrgences = new ArrayCollection();
        $this->conseilsUtilisateurs = new ArrayCollection();
        $this->doctors = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        $this->forumPosts = new ArrayCollection();
        $this->humeurs = new ArrayCollection();
        $this->patients = new ArrayCollection();
        $this->reponses = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->santeBienEtres = new ArrayCollection();
        $this->sleepTrackings = new ArrayCollection();
        $this->tests = new ArrayCollection();
        $this->ressources = new ArrayCollection();
    }

    /**
     * @return Collection<int, Ressource>
     */
    public function getRessources(): Collection
    {
        if (!$this->ressources instanceof Collection) {
            $this->ressources = new ArrayCollection();
        }
        return $this->ressources;
    }

    public function addRessource(Ressource $ressource): self
    {
        if (!$this->getRessources()->contains($ressource)) {
            $this->getRessources()->add($ressource);
        }
        return $this;
    }

    public function removeRessource(Ressource $ressource): self
    {
        $this->getRessources()->removeElement($ressource);
        return $this;
    }

    public function getSecondName(): ?string
    {
        return $this->second_name;
    }

    public function setSecondName(string $second_name): static
    {
        $this->second_name = $second_name;

        return $this;
    }

    public function getPhoneNumber(): ?int
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(?int $phone_number): static
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getBirthDate(): ?string
    {
        return $this->birth_date;
    }

    public function setBirthDate(?string $birth_date): static
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    public function getIsBlocked(): ?int
    {
        return $this->is_blocked;
    }

    public function setIsBlocked(?int $is_blocked): static
    {
        $this->is_blocked = $is_blocked;

        return $this;
    }
        public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return [$this->role]; // ou ['ROLE_USER']
    }

    public function eraseCredentials(): void
    {
        // nothing for now
    }
}