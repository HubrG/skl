<?php

namespace App\Entity;

use App\Repository\PublicationReadRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationReadRepository::class)]
class PublicationRead
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationReads')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'publicationReads')]
    private ?PublicationChapter $chapter = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $ReadAt = null;

    #[ORM\ManyToOne(inversedBy: 'publicationReads')]
    private ?Publication $publication = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getChapter(): ?PublicationChapter
    {
        return $this->chapter;
    }

    public function setChapter(?PublicationChapter $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->ReadAt;
    }

    public function setReadAt(?\DateTimeImmutable $ReadAt): self
    {
        $this->ReadAt = $ReadAt;

        return $this;
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
}
