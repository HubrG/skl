<?php

namespace App\Entity;

use App\Repository\InboxRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InboxRepository::class)]
class Inbox
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inboxes')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'inboxes')]
    private ?User $UserTo = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $CreatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $ReadAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'inboxes')]
    private ?InboxGroup $grouped = null;

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

    public function getUserTo(): ?User
    {
        return $this->UserTo;
    }

    public function setUserTo(?User $UserTo): self
    {
        $this->UserTo = $UserTo;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getGrouped(): ?InboxGroup
    {
        return $this->grouped;
    }

    public function setGrouped(?InboxGroup $grouped): self
    {
        $this->grouped = $grouped;

        return $this;
    }
}
