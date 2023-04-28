<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\PublicationChapterRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: PublicationChapterRepository::class)]
class PublicationChapter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapters')]
    private ?Publication $publication = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column(nullable: true)]
    private ?int $order_display = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $published = null;

    #[ORM\OneToMany(mappedBy: 'chapter', orphanRemoval: true, targetEntity: PublicationChapterVersioning::class)]
    private Collection $publicationChapterVersionings;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;


    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationChapterView::class, orphanRemoval: true)]
    private Collection $publicationChapterViews;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationChapterNote::class, orphanRemoval: true)]
    private Collection $publicationChapterNotes;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationChapterLike::class, orphanRemoval: true)]
    private Collection $publicationChapterLikes;


    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationComment::class, orphanRemoval: true)]
    private Collection $publicationComments;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationBookmark::class, orphanRemoval: true)]
    private Collection $publicationBookmarks;

    #[ORM\OneToMany(mappedBy: 'publication_follow', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdf = null;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: Pictures::class, orphanRemoval: true)]
    private Collection $pictures;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3)]
    private ?string $pop = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $trashAt = null;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationRead::class)]
    private Collection $publicationReads;

    #[ORM\Column(nullable: true)]
    private ?int $wordCount = null;

    public function __construct()
    {
        $this->publicationChapterVersionings = new ArrayCollection();
        $this->publicationChapterViews = new ArrayCollection();
        $this->publicationChapterNotes = new ArrayCollection();
        $this->publicationChapterLikes = new ArrayCollection();
        $this->publicationComments = new ArrayCollection();
        $this->publicationBookmarks = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->publicationReads = new ArrayCollection();
    }




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderDisplay(): ?int
    {
        return $this->order_display;
    }

    public function setOrderDisplay(?int $order_display): self
    {
        $this->order_display = $order_display;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(?\DateTimeInterface $published): self
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return Collection<int, PublicationChapterVersioning>
     */
    public function getPublicationChapterVersionings(): Collection
    {
        return $this->publicationChapterVersionings;
    }

    public function addPublicationChapterVersioning(PublicationChapterVersioning $publicationChapterVersioning): self
    {
        if (!$this->publicationChapterVersionings->contains($publicationChapterVersioning)) {
            $this->publicationChapterVersionings->add($publicationChapterVersioning);
            $publicationChapterVersioning->setChapter($this);
        }

        return $this;
    }

    public function removePublicationChapterVersioning(PublicationChapterVersioning $publicationChapterVersioning): self
    {
        if ($this->publicationChapterVersionings->removeElement($publicationChapterVersioning)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterVersioning->getChapter() === $this) {
                $publicationChapterVersioning->setChapter(null);
            }
        }

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



    /**
     * @return Collection<int, PublicationChapterView>
     */
    public function getPublicationChapterViews(): Collection
    {
        return $this->publicationChapterViews;
    }

    public function addPublicationChapterView(PublicationChapterView $publicationChapterView): self
    {
        if (!$this->publicationChapterViews->contains($publicationChapterView)) {
            $this->publicationChapterViews->add($publicationChapterView);
            $publicationChapterView->setChapter($this);
        }

        return $this;
    }

    public function removePublicationChapterView(PublicationChapterView $publicationChapterView): self
    {
        if ($this->publicationChapterViews->removeElement($publicationChapterView)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterView->getChapter() === $this) {
                $publicationChapterView->setChapter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationChapterNote>
     */
    public function getPublicationChapterNotes(): Collection
    {
        return $this->publicationChapterNotes;
    }

    public function addPublicationChapterNote(PublicationChapterNote $publicationChapterNote): self
    {
        if (!$this->publicationChapterNotes->contains($publicationChapterNote)) {
            $this->publicationChapterNotes->add($publicationChapterNote);
            $publicationChapterNote->setChapter($this);
        }

        return $this;
    }

    public function removePublicationChapterNote(PublicationChapterNote $publicationChapterNote): self
    {
        if ($this->publicationChapterNotes->removeElement($publicationChapterNote)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterNote->getChapter() === $this) {
                $publicationChapterNote->setChapter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationChapterLike>
     */
    public function getPublicationChapterLikes(): Collection
    {
        return $this->publicationChapterLikes;
    }

    public function addPublicationChapterLike(PublicationChapterLike $publicationChapterLike): self
    {
        if (!$this->publicationChapterLikes->contains($publicationChapterLike)) {
            $this->publicationChapterLikes->add($publicationChapterLike);
            $publicationChapterLike->setChapter($this);
        }

        return $this;
    }

    public function removePublicationChapterLike(PublicationChapterLike $publicationChapterLike): self
    {
        if ($this->publicationChapterLikes->removeElement($publicationChapterLike)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterLike->getChapter() === $this) {
                $publicationChapterLike->setChapter(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, PublicationComment>
     */
    public function getPublicationComments(): Collection
    {
        return $this->publicationComments;
    }

    public function addPublicationComment(PublicationComment $publicationComment): self
    {
        if (!$this->publicationComments->contains($publicationComment)) {
            $this->publicationComments->add($publicationComment);
            $publicationComment->setChapter($this);
        }

        return $this;
    }

    public function removePublicationComment(PublicationComment $publicationComment): self
    {
        if ($this->publicationComments->removeElement($publicationComment)) {
            // set the owning side to null (unless already changed)
            if ($publicationComment->getChapter() === $this) {
                $publicationComment->setChapter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationBookmark>
     */
    public function getPublicationBookmarks(): Collection
    {
        return $this->publicationBookmarks;
    }

    public function addPublicationBookmark(PublicationBookmark $publicationBookmark): self
    {
        if (!$this->publicationBookmarks->contains($publicationBookmark)) {
            $this->publicationBookmarks->add($publicationBookmark);
            $publicationBookmark->setChapter($this);
        }

        return $this;
    }

    public function removePublicationBookmark(PublicationBookmark $publicationBookmark): self
    {
        if ($this->publicationBookmarks->removeElement($publicationBookmark)) {
            // set the owning side to null (unless already changed)
            if ($publicationBookmark->getChapter() === $this) {
                $publicationBookmark->setChapter(null);
            }
        }

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
            $notification->setPublicationFollow($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getPublicationFollow() === $this) {
                $notification->setPublicationFollow(null);
            }
        }

        return $this;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setPdf(?string $pdf): self
    {
        $this->pdf = $pdf;

        return $this;
    }

    /**
     * @return Collection<int, Pictures>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Pictures $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setChapter($this);
        }

        return $this;
    }

    public function removePicture(Pictures $picture): self
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getChapter() === $this) {
                $picture->setChapter(null);
            }
        }

        return $this;
    }

    public function getPop(): ?string
    {
        return $this->pop;
    }

    public function setPop(string $pop): self
    {
        $this->pop = $pop;

        return $this;
    }
    public function __toString()
    {
        // Si le titre est null, retourner une chaîne vide ou une valeur par défaut
        return $this->title ?? '';
    }

    public function getTrashAt(): ?\DateTimeImmutable
    {
        return $this->trashAt;
    }

    public function setTrashAt(?\DateTimeImmutable $trashAt): self
    {
        $this->trashAt = $trashAt;

        return $this;
    }

    /**
     * @return Collection<int, PublicationRead>
     */
    public function getPublicationReads(): Collection
    {
        return $this->publicationReads;
    }

    public function addPublicationRead(PublicationRead $publicationRead): self
    {
        if (!$this->publicationReads->contains($publicationRead)) {
            $this->publicationReads->add($publicationRead);
            $publicationRead->setChapter($this);
        }

        return $this;
    }

    public function removePublicationRead(PublicationRead $publicationRead): self
    {
        if ($this->publicationReads->removeElement($publicationRead)) {
            // set the owning side to null (unless already changed)
            if ($publicationRead->getChapter() === $this) {
                $publicationRead->setChapter(null);
            }
        }

        return $this;
    }

    public function getWordCount(): ?int
    {
        return $this->wordCount;
    }

    public function setWordCount(?int $wordCount): self
    {
        $this->wordCount = $wordCount;

        return $this;
    }
}
