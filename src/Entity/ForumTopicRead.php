<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ForumTopicReadRepository;

#[ORM\Entity(repositoryClass: ForumTopicReadRepository::class)]
class ForumTopicRead
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'forumTopicReads')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'forumTopicReads')]
    private ?ForumTopic $topic = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $readAt = null;



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

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(\DateTimeImmutable $readAt): self
    {
        $this->readAt = $readAt;

        return $this;
    }
}
