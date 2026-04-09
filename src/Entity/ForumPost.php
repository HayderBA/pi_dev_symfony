<?php

namespace App\Entity;

use App\Repository\ForumPostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ForumPostRepository::class)]
#[ORM\Table(name: 'forum_posts')]
class ForumPost
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
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Le nom doit contenir au moins 2 caractères")]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le rôle est obligatoire")]
    private ?string $role = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire")]
    private ?string $categorie = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu est obligatoire")]
    #[Assert\Length(min: 20, minMessage: "Le contenu doit contenir au moins 20 caractères")]
    private ?string $contenu = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column]
    private ?bool $archive = false;

    #[ORM\Column]
    private ?int $likes = 0;

    #[ORM\Column]
    private ?int $vues = 0;

    #[ORM\Column]
    private ?int $dislikes = 0;

    // Méthode privée pour filtrer un texte
    private function filterBadWords(string $text): string
    {
        $filtered = $text;
        foreach (self::BAD_WORDS as $word) {
            $replacement = str_repeat('*', mb_strlen($word));
            // Remplacer le mot entier (limité par les limites de mot) insensible à la casse
            $filtered = preg_replace('/\b' . preg_quote($word, '/') . '\b/ui', $replacement, $filtered);
        }
        return $filtered;
    }

    // Setters avec filtrage automatique
    public function setNom(string $nom): self
    {
        $this->nom = $this->filterBadWords($nom);
        return $this;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $this->filterBadWords($contenu);
        return $this;
    }

    // Les autres setters (role, categorie, etc.) restent inchangés
    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function setArchive(bool $archive): self
    {
        $this->archive = $archive;
        return $this;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;
        return $this;
    }

    public function setVues(int $vues): self
    {
        $this->vues = $vues;
        return $this;
    }

    public function setDislikes(int $dislikes): self
    {
        $this->dislikes = $dislikes;
        return $this;
    }

    // Getters inchangés
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function getRole(): ?string { return $this->role; }
    public function getCategorie(): ?string { return $this->categorie; }
    public function getContenu(): ?string { return $this->contenu; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function isArchive(): ?bool { return $this->archive; }
    public function getLikes(): ?int { return $this->likes; }
    public function getVues(): ?int { return $this->vues; }
    public function getDislikes(): ?int { return $this->dislikes; }
}