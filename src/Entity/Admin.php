<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdminRepository;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: 'admins')]
class Admin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_user = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'admins')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $actif = null;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $face_image = null;



    // ======================
    // GETTERS / SETTERS
    // ======================

    public function getId_user(): ?int
    {
        return $this->id_user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getActif(): ?int
    {
        return $this->actif;
    }

    public function setActif(int $actif): self
    {
        $this->actif = $actif;
        return $this;
    }
    public function getFaceImage(): ?string
    {
        return $this->face_image;
    }

    public function setFaceImage(?string $face_image): self
    {
        $this->face_image = $face_image;
        return $this;
    }
}