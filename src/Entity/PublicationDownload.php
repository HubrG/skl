<?php

namespace App\Entity;

use App\Repository\PublicationDownloadRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationDownloadRepository::class)]
class PublicationDownload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationDownloads')]
    private ?Publication $publication = null;

    #[ORM\ManyToOne(inversedBy: 'publicationDownloads')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dlAt = null;

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

    public function getDlAt(): ?\DateTimeImmutable
    {
        return $this->dlAt;
    }

    public function setDlAt(?\DateTimeImmutable $dlAt): self
    {
        $this->dlAt = $dlAt;

        return $this;
    }
}
