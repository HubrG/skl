<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\PublicationBookmarkRepository;

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

    #[ORM\OneToMany(mappedBy: 'publication_bookmark', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'publicationChapterBookmark', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notifcationPublicationChapterBookmark;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->notifcationPublicationChapterBookmark = new ArrayCollection();
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
            $notification->setPublicationBookmark($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getPublicationBookmark() === $this) {
                $notification->setPublicationBookmark(null);
            }
        }

        return $this;
    }
    public function getTimestamp(): int
    {
        return $this->createdAt->getTimestamp();
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifcationPublicationChapterBookmark(): Collection
    {
        return $this->notifcationPublicationChapterBookmark;
    }

    public function addNotifcationPublicationChapterBookmark(Notification $notifcationPublicationChapterBookmark): self
    {
        if (!$this->notifcationPublicationChapterBookmark->contains($notifcationPublicationChapterBookmark)) {
            $this->notifcationPublicationChapterBookmark->add($notifcationPublicationChapterBookmark);
            $notifcationPublicationChapterBookmark->setPublicationChapterBookmark($this);
        }

        return $this;
    }

    public function removeNotifcationPublicationChapterBookmark(Notification $notifcationPublicationChapterBookmark): self
    {
        if ($this->notifcationPublicationChapterBookmark->removeElement($notifcationPublicationChapterBookmark)) {
            // set the owning side to null (unless already changed)
            if ($notifcationPublicationChapterBookmark->getPublicationChapterBookmark() === $this) {
                $notifcationPublicationChapterBookmark->setPublicationChapterBookmark(null);
            }
        }

        return $this;
    }
}
