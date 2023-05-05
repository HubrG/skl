<?php

namespace App\Entity;

use App\Repository\ForumTopicViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForumTopicViewRepository::class)]
class ForumTopicView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'forumTopicViews')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'forumTopicViews')]
    private ?ForumTopic $topic = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $viewDate = null;

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

    public function getTopic(): ?ForumTopic
    {
        return $this->topic;
    }

    public function setTopic(?ForumTopic $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getViewDate(): ?\DateTimeImmutable
    {
        return $this->viewDate;
    }

    public function setViewDate(?\DateTimeImmutable $viewDate): self
    {
        $this->viewDate = $viewDate;

        return $this;
    }
}
