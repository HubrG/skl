<?php

namespace App\Entity;

use App\Repository\PublicationBookmarkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationBookmarkRepository::class)]
class PublicationBookmark
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationBookmarks')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'publicationBookmarks')]
    private ?Publication $publication = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'publicationBookmarks')]
    private ?PublicationChapter $chapter = null;

    #[ORM\ManyToOne(inversedBy: 'publicationBookmarks')]
    private ?PublicationBookmarkCollection $collection = null;

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

    public function getPublication(): ?Publication
    {
        return $this->publication;
    }

    public function setPublication(?Publication $publication): self
    {
        $this->publication = $publication;

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

    public function getChapter(): ?PublicationChapter
    {
        return $this->chapter;
    }

    public function setChapter(?PublicationChapter $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getCollection(): ?PublicationBookmarkCollection
    {
        return $this->collection;
    }

    public function setCollection(?PublicationBookmarkCollection $collection): self
    {
        $this->collection = $collection;

        return $this;
    }
}
