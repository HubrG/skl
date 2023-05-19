<?php

namespace App\Entity;

use App\Repository\InboxGroupMemberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InboxGroupMemberRepository::class)]
class InboxGroupMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inboxGroupMembers')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'inboxGroupMembers')]
    private ?InboxGroup $grouped = null;

    #[ORM\Column(nullable: true)]
    private ?int $unread = null;

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

    public function getGrouped(): ?InboxGroup
    {
        return $this->grouped;
    }

    public function setGrouped(?InboxGroup $grouped): self
    {
        $this->grouped = $grouped;

        return $this;
    }

    public function getUnread(): ?int
    {
        return $this->unread;
    }

    public function setUnread(?int $unread): self
    {
        $this->unread = $unread;

        return $this;
    }
}
