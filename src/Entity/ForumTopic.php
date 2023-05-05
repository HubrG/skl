<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ForumTopicRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ForumTopicRepository::class)]
class ForumTopic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'forumTopics')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'forumTopics')]
    private ?ForumCategory $category = null;

    #[ORM\OneToMany(mappedBy: 'topic', targetEntity: ForumMessage::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $forumMessages;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?bool $permanent = null;

    #[ORM\OneToMany(mappedBy: 'topic', targetEntity: ForumTopicRead::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $forumTopicReads;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'topic', targetEntity: ForumTopicView::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $forumTopicViews;


    public function __construct()
    {
        $this->forumMessages = new ArrayCollection();
        $this->forumTopicReads = new ArrayCollection();
        $this->forumTopicViews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?ForumCategory
    {
        return $this->category;
    }

    public function setCategory(?ForumCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, ForumMessage>
     */
    public function getForumMessages(): Collection
    {
        return $this->forumMessages;
    }

    public function addForumMessage(ForumMessage $forumMessage): self
    {
        if (!$this->forumMessages->contains($forumMessage)) {
            $this->forumMessages->add($forumMessage);
            $forumMessage->setTopic($this);
        }

        return $this;
    }

    public function removeForumMessage(ForumMessage $forumMessage): self
    {
        if ($this->forumMessages->removeElement($forumMessage)) {
            // set the owning side to null (unless already changed)
            if ($forumMessage->getTopic() === $this) {
                $forumMessage->setTopic(null);
            }
        }

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function isPermanent(): ?bool
    {
        return $this->permanent;
    }

    public function setPermanent(bool $permanent): self
    {
        $this->permanent = $permanent;

        return $this;
    }

    /**
     * @return Collection<int, ForumTopicRead>
     */
    public function getForumTopicReads(): Collection
    {
        return $this->forumTopicReads;
    }

    public function addForumTopicRead(ForumTopicRead $forumTopicRead): self
    {
        if (!$this->forumTopicReads->contains($forumTopicRead)) {
            $this->forumTopicReads->add($forumTopicRead);
            $forumTopicRead->setTopic($this);
        }

        return $this;
    }

    public function removeForumTopicRead(ForumTopicRead $forumTopicRead): self
    {
        if ($this->forumTopicReads->removeElement($forumTopicRead)) {
            // set the owning side to null (unless already changed)
            if ($forumTopicRead->getTopic() === $this) {
                $forumTopicRead->setTopic(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ForumTopicView>
     */
    public function getForumTopicViews(): Collection
    {
        return $this->forumTopicViews;
    }

    public function addForumTopicView(ForumTopicView $forumTopicView): self
    {
        if (!$this->forumTopicViews->contains($forumTopicView)) {
            $this->forumTopicViews->add($forumTopicView);
            $forumTopicView->setTopic($this);
        }

        return $this;
    }

    public function removeForumTopicView(ForumTopicView $forumTopicView): self
    {
        if ($this->forumTopicViews->removeElement($forumTopicView)) {
            // set the owning side to null (unless already changed)
            if ($forumTopicView->getTopic() === $this) {
                $forumTopicView->setTopic(null);
            }
        }

        return $this;
    }
}
