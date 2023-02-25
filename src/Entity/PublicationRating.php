<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PublicationRatingRepository;

#[ORM\Entity(repositoryClass: PublicationRatingRepository::class)]
class PublicationRating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationRatings')]
    private ?Publication $publication = null;

    #[ORM\ManyToOne(inversedBy: 'publicationRatings')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?int $note = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublication(): ?Publication
    {
        return $this->publication;
    }

    public function setPublication(?Publication $publication): self
    {
        $this->publication = $publication;

        return $this;
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

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
