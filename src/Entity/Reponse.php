<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
#[ORM\Table(name: 'reponses')]
class Reponse
{
    private const BAD_WORDS = [
        'con', 'pute', 'merde', 'salope', 'enculé', 'bordel',
        'connard', 'idiot', 'stupide', 'putain', 'fuck', 'shit'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'auteur est obligatoire")]
    #[Assert\Length(min: 2, max: 100, minMessage: "L'auteur doit contenir au moins 2 caractères")]
    private ?string $auteur = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu est obligatoire")]
    #[Assert\Length(min: 5, minMessage: "Le contenu doit contenir au moins 5 caractères")]
    private ?string $contenu = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column]
    private ?int $likes = 0;

    #[ORM\Column]
    private ?int $dislikes = 0;

    #[ORM\ManyToOne(targetEntity: ForumPost::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ForumPost $forumPost = null;

    private function filterBadWords(string $text): string
    {
        $filtered = $text;
        foreach (self::BAD_WORDS as $word) {
            $replacement = str_repeat('*', mb_strlen($word));
            $filtered = preg_replace('/\b' . preg_quote($word, '/') . '\b/ui', $replacement, $filtered);
        }
        return $filtered;
    }

    // Setters avec filtrage
    public function setAuteur(string $auteur): self
    {
        $this->auteur = $this->filterBadWords($auteur);
        return $this;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $this->filterBadWords($contenu);
        return $this;
    }

    // Getters / Setters standards
    public function getId(): ?int { return $this->id; }
    public function getAuteur(): ?string { return $this->auteur; }
    public function getContenu(): ?string { return $this->contenu; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function setDateCreation(\DateTimeImmutable $dateCreation): self { $this->dateCreation = $dateCreation; return $this; }
    public function getLikes(): ?int { return $this->likes; }
    public function setLikes(int $likes): self { $this->likes = $likes; return $this; }
    public function getDislikes(): ?int { return $this->dislikes; }
    public function setDislikes(int $dislikes): self { $this->dislikes = $dislikes; return $this; }
    public function getForumPost(): ?ForumPost { return $this->forumPost; }
    public function setForumPost(?ForumPost $forumPost): self { $this->forumPost = $forumPost; return $this; }
}