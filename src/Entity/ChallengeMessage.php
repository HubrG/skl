<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\ChallengeMessageRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ChallengeMessageRepository::class)]
class ChallengeMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'challengeMessages')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'challengeMessages')]
    private ?Challenge $challenge = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $replyToId = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'challengeMessages')]
    private ?self $replyTo = null;

    #[ORM\OneToMany(mappedBy: 'replyTo', targetEntity: self::class, orphanRemoval: true)]
    private Collection $challengeMessages;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: ChallengeMessageLike::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $challengeMessageLikes;

    #[ORM\OneToMany(mappedBy: 'challengeMessage', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notificationChallengeMessages;

    #[ORM\OneToMany(mappedBy: 'assignChallengeMessage', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notificationAssignChallengeMessages;

    #[ORM\OneToMany(mappedBy: 'likeChallenge', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notificationChallengeLikes;

    #[ORM\OneToMany(mappedBy: 'challengeMessageReply', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notificationChallengeMessageReply;

    public function __construct()
    {
        $this->challengeMessages = new ArrayCollection();
        $this->challengeMessageLikes = new ArrayCollection();
        $this->notificationChallengeMessages = new ArrayCollection();
        $this->notificationAssignChallengeMessages = new ArrayCollection();
        $this->notificationChallengeLikes = new ArrayCollection();
        $this->notificationChallengeMessageReply = new ArrayCollection();
    }

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

    public function getChallenge(): ?Challenge
    {
        return $this->challenge;
    }

    public function setChallenge(?Challenge $challenge): self
    {
        $this->challenge = $challenge;

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

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

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

    public function getReplyToId(): ?int
    {
        return $this->replyToId;
    }

    public function setReplyToId(int $replyToId): self
    {
        $this->replyToId = $replyToId;

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
    public function getChallengeMessages(): Collection
    {
        return $this->challengeMessages;
    }

    public function addChallengeMessage(self $challengeMessage): self
    {
        if (!$this->challengeMessages->contains($challengeMessage)) {
            $this->challengeMessages->add($challengeMessage);
            $challengeMessage->setReplyTo($this);
        }

        return $this;
    }

    public function removeChallengeMessage(self $challengeMessage): self
    {
        if ($this->challengeMessages->removeElement($challengeMessage)) {
            // set the owning side to null (unless already changed)
            if ($challengeMessage->getReplyTo() === $this) {
                $challengeMessage->setReplyTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChallengeMessageLike>
     */
    public function getChallengeMessageLikes(): Collection
    {
        return $this->challengeMessageLikes;
    }

    public function addChallengeMessageLike(ChallengeMessageLike $challengeMessageLike): self
    {
        if (!$this->challengeMessageLikes->contains($challengeMessageLike)) {
            $this->challengeMessageLikes->add($challengeMessageLike);
            $challengeMessageLike->setMessage($this);
        }

        return $this;
    }

    public function removeChallengeMessageLike(ChallengeMessageLike $challengeMessageLike): self
    {
        if ($this->challengeMessageLikes->removeElement($challengeMessageLike)) {
            // set the owning side to null (unless already changed)
            if ($challengeMessageLike->getMessage() === $this) {
                $challengeMessageLike->setMessage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationChallengeMessages(): Collection
    {
        return $this->notificationChallengeMessages;
    }

    public function addNotificationChallengeMessage(Notification $notificationChallengeMessage): self
    {
        if (!$this->notificationChallengeMessages->contains($notificationChallengeMessage)) {
            $this->notificationChallengeMessages->add($notificationChallengeMessage);
            $notificationChallengeMessage->setChallengeMessage($this);
        }

        return $this;
    }

    public function removeNotificationChallengeMessage(Notification $notificationChallengeMessage): self
    {
        if ($this->notificationChallengeMessages->removeElement($notificationChallengeMessage)) {
            // set the owning side to null (unless already changed)
            if ($notificationChallengeMessage->getChallengeMessage() === $this) {
                $notificationChallengeMessage->setChallengeMessage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationAssignChallengeMessages(): Collection
    {
        return $this->notificationAssignChallengeMessages;
    }

    public function addNotificationAssignChallengeMessage(Notification $notificationAssignChallengeMessage): self
    {
        if (!$this->notificationAssignChallengeMessages->contains($notificationAssignChallengeMessage)) {
            $this->notificationAssignChallengeMessages->add($notificationAssignChallengeMessage);
            $notificationAssignChallengeMessage->setAssignChallengeMessage($this);
        }

        return $this;
    }

    public function removeNotificationAssignChallengeMessage(Notification $notificationAssignChallengeMessage): self
    {
        if ($this->notificationAssignChallengeMessages->removeElement($notificationAssignChallengeMessage)) {
            // set the owning side to null (unless already changed)
            if ($notificationAssignChallengeMessage->getAssignChallengeMessage() === $this) {
                $notificationAssignChallengeMessage->setAssignChallengeMessage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationChallengeLikes(): Collection
    {
        return $this->notificationChallengeLikes;
    }

    public function addNotificationChallengeLike(Notification $notificationChallengeLike): self
    {
        if (!$this->notificationChallengeLikes->contains($notificationChallengeLike)) {
            $this->notificationChallengeLikes->add($notificationChallengeLike);
            $notificationChallengeLike->setLikeChallenge($this);
        }

        return $this;
    }

    public function removeNotificationChallengeLike(Notification $notificationChallengeLike): self
    {
        if ($this->notificationChallengeLikes->removeElement($notificationChallengeLike)) {
            // set the owning side to null (unless already changed)
            if ($notificationChallengeLike->getLikeChallenge() === $this) {
                $notificationChallengeLike->setLikeChallenge(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationChallengeMessageReply(): Collection
    {
        return $this->notificationChallengeMessageReply;
    }

    public function addNotificationChallengeMessageReply(Notification $notificationChallengeMessageReply): self
    {
        if (!$this->notificationChallengeMessageReply->contains($notificationChallengeMessageReply)) {
            $this->notificationChallengeMessageReply->add($notificationChallengeMessageReply);
            $notificationChallengeMessageReply->setChallengeMessageReply($this);
        }

        return $this;
    }

    public function removeNotificationChallengeMessageReply(Notification $notificationChallengeMessageReply): self
    {
        if ($this->notificationChallengeMessageReply->removeElement($notificationChallengeMessageReply)) {
            // set the owning side to null (unless already changed)
            if ($notificationChallengeMessageReply->getChallengeMessageReply() === $this) {
                $notificationChallengeMessageReply->setChallengeMessageReply(null);
            }
        }

        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->publishedAt->getTimestamp();
    }
}
