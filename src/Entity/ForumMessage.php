<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ForumMessageRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ForumMessageRepository::class)]
class ForumMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $published_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'forumMessages')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'forumMessages')]
    private ?ForumTopic $topic = null;

    #[ORM\OneToMany(mappedBy: 'forumMessage', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'assignForumMessage', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notificationAssigns;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'forumMessages')]
    private ?self $replyTo = null;

    #[ORM\OneToMany(mappedBy: 'replyTo', targetEntity: self::class, orphanRemoval: true)]
    private Collection $forumMessages;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: ForumMessageLike::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $forumMessageLikes;

    #[ORM\OneToMany(mappedBy: 'replyForum', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notificationReplies;

    #[ORM\OneToMany(mappedBy: 'likeForum', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notificationLikes;

    #[ORM\OneToMany(mappedBy: 'assignForumReply', targetEntity: Notification::class)]
    private Collection $notificationAssignForumMessages;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->notificationAssigns = new ArrayCollection();
        $this->forumMessages = new ArrayCollection();
        $this->forumMessageLikes = new ArrayCollection();
        $this->notificationReplies = new ArrayCollection();
        $this->notificationLikes = new ArrayCollection();
        $this->notificationAssignForumMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->published_at;
    }

    public function setPublishedAt(?\DateTimeImmutable $published_at): self
    {
        $this->published_at = $published_at;

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

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setForumMessage($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getForumMessage() === $this) {
                $notification->setForumMessage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationAssigns(): Collection
    {
        return $this->notificationAssigns;
    }

    public function addNotificationAssign(Notification $notificationAssign): self
    {
        if (!$this->notificationAssigns->contains($notificationAssign)) {
            $this->notificationAssigns->add($notificationAssign);
            $notificationAssign->setAssignForumMessage($this);
        }

        return $this;
    }

    public function removeNotificationAssign(Notification $notificationAssign): self
    {
        if ($this->notificationAssigns->removeElement($notificationAssign)) {
            // set the owning side to null (unless already changed)
            if ($notificationAssign->getAssignForumMessage() === $this) {
                $notificationAssign->setAssignForumMessage(null);
            }
        }

        return $this;
    }
    public function getReplyTo(): ?self
    {
        return $this->replyTo;
    }

    public function setReplyTo(?self $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }
    /**
     * @return Collection<int, self>
     */
    public function getForumMessages(): Collection
    {
        return $this->forumMessages;
    }

    public function addForumMessages(self $forumMessages): self
    {
        if (!$this->forumMessages->contains($forumMessages)) {
            $this->forumMessages->add($forumMessages);
            $forumMessages->setReplyTo($this);
        }

        return $this;
    }

    public function removeForumMessages(self $forumMessages): self
    {
        if ($this->forumMessages->removeElement($forumMessages)) {
            // set the owning side to null (unless already changed)
            if ($forumMessages->getReplyTo() === $this) {
                $forumMessages->setReplyTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ForumMessageLike>
     */
    public function getForumMessageLikes(): Collection
    {
        return $this->forumMessageLikes;
    }

    public function addForumMessageLike(ForumMessageLike $forumMessageLike): self
    {
        if (!$this->forumMessageLikes->contains($forumMessageLike)) {
            $this->forumMessageLikes->add($forumMessageLike);
            $forumMessageLike->setMessage($this);
        }

        return $this;
    }

    public function removeForumMessageLike(ForumMessageLike $forumMessageLike): self
    {
        if ($this->forumMessageLikes->removeElement($forumMessageLike)) {
            // set the owning side to null (unless already changed)
            if ($forumMessageLike->getMessage() === $this) {
                $forumMessageLike->setMessage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationReplies(): Collection
    {
        return $this->notificationReplies;
    }

    public function addNotificationReply(Notification $notificationReply): self
    {
        if (!$this->notificationReplies->contains($notificationReply)) {
            $this->notificationReplies->add($notificationReply);
            $notificationReply->setReplyForum($this);
        }

        return $this;
    }

    public function removeNotificationReply(Notification $notificationReply): self
    {
        if ($this->notificationReplies->removeElement($notificationReply)) {
            // set the owning side to null (unless already changed)
            if ($notificationReply->getReplyForum() === $this) {
                $notificationReply->setReplyForum(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationLikes(): Collection
    {
        return $this->notificationLikes;
    }

    public function addNotificationLike(Notification $notificationLike): self
    {
        if (!$this->notificationLikes->contains($notificationLike)) {
            $this->notificationLikes->add($notificationLike);
            $notificationLike->setLikeForum($this);
        }

        return $this;
    }

    public function removeNotificationLike(Notification $notificationLike): self
    {
        if ($this->notificationLikes->removeElement($notificationLike)) {
            // set the owning side to null (unless already changed)
            if ($notificationLike->getLikeForum() === $this) {
                $notificationLike->setLikeForum(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationAssignForumMessages(): Collection
    {
        return $this->notificationAssignForumMessages;
    }

    public function addNotificationAssignForumMessage(Notification $notificationAssignForumMessage): self
    {
        if (!$this->notificationAssignForumMessages->contains($notificationAssignForumMessage)) {
            $this->notificationAssignForumMessages->add($notificationAssignForumMessage);
            $notificationAssignForumMessage->setAssignForumReply($this);
        }

        return $this;
    }

    public function removeNotificationAssignForumMessage(Notification $notificationAssignForumMessage): self
    {
        if ($this->notificationAssignForumMessages->removeElement($notificationAssignForumMessage)) {
            // set the owning side to null (unless already changed)
            if ($notificationAssignForumMessage->getAssignForumReply() === $this) {
                $notificationAssignForumMessage->setAssignForumReply(null);
            }
        }

        return $this;
    }
}
