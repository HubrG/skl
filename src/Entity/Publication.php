<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'publications')]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationChapter::class, orphanRemoval: true)]
    private Collection $publicationChapters;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover = null;

    #[ORM\Column]
    private ?bool $mature = null;

    #[ORM\ManyToOne(inversedBy: 'publications')]
    private ?PublicationCategory $category = null;

    #[ORM\ManyToMany(targetEntity: PublicationKeyword::class, mappedBy: 'publication')]
    private Collection $publicationKeywords;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $published_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;


    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3, nullable: true)]
    private ?string $pop = null;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationDownload::class, orphanRemoval: true)]
    private Collection $publicationDownloads;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationPopularity::class, orphanRemoval: true)]
    private Collection $publicationPopularities;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationComment::class, orphanRemoval: true)]
    private Collection $publicationComments;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationBookmark::class, orphanRemoval: true)]
    private Collection $publicationBookmarks;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationRating::class, orphanRemoval: true)]
    private Collection $publicationRatings;

    #[ORM\Column(nullable: true)]
    private ?bool $finished = null;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationFollow::class, orphanRemoval: true)]
    private Collection $publicationFollows;

    #[ORM\OneToMany(mappedBy: 'publication_follow_add', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastPublishedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sale_paper = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sale_web = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sale = null;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationRead::class)]
    private Collection $publicationReads;

    #[ORM\Column(nullable: true)]
    private ?int $wordCount = null;





    public function __construct()
    {
        $this->publicationChapters = new ArrayCollection();
        $this->publicationKeywords = new ArrayCollection();
        $this->publicationDownloads = new ArrayCollection();
        $this->publicationPopularities = new ArrayCollection();
        $this->publicationComments = new ArrayCollection();
        $this->publicationBookmarks = new ArrayCollection();
        $this->publicationRatings = new ArrayCollection();
        $this->publicationFollows = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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

    public function setTitle(?string $title): self
    {
        $this->title = $title;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, PublicationChapter>
     */
    public function getPublicationChapters(): Collection
    {
        return $this->publicationChapters;
    }

    public function addPublicationChapter(PublicationChapter $publicationChapter): self
    {
        if (!$this->publicationChapters->contains($publicationChapter)) {
            $this->publicationChapters->add($publicationChapter);
            $publicationChapter->setPublication($this);
        }

        return $this;
    }

    public function removePublicationChapter(PublicationChapter $publicationChapter): self
    {
        if ($this->publicationChapters->removeElement($publicationChapter)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapter->getPublication() === $this) {
                $publicationChapter->setPublication(null);
            }
        }

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(?string $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    public function isMature(): ?bool
    {
        return $this->mature;
    }

    public function setMature(bool $mature): self
    {
        $this->mature = $mature;

        return $this;
    }

    public function getCategory(): ?PublicationCategory
    {
        return $this->category;
    }

    public function setCategory(?PublicationCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, PublicationKeyword>
     */
    public function getPublicationKeywords(): Collection
    {
        return $this->publicationKeywords;
    }

    public function addPublicationKeyword(PublicationKeyword $publicationKeyword): self
    {
        if (!$this->publicationKeywords->contains($publicationKeyword)) {
            $this->publicationKeywords->add($publicationKeyword);
            $publicationKeyword->addPublication($this);
        }

        return $this;
    }

    public function removePublicationKeyword(PublicationKeyword $publicationKeyword): self
    {
        if ($this->publicationKeywords->removeElement($publicationKeyword)) {
            $publicationKeyword->removePublication($this);
        }

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

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(?\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getPublishedDate(): ?\DateTimeInterface
    {
        return $this->published_date;
    }

    public function setPublishedDate(\DateTimeInterface $published_date): self
    {
        $this->published_date = $published_date;

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


    public function getPop(): ?string
    {
        return $this->pop;
    }

    public function setPop(?string $pop): self
    {
        $this->pop = $pop;

        return $this;
    }

    /**
     * @return Collection<int, PublicationDownload>
     */
    public function getPublicationDownloads(): Collection
    {
        return $this->publicationDownloads;
    }

    public function addPublicationDownload(PublicationDownload $publicationDownload): self
    {
        if (!$this->publicationDownloads->contains($publicationDownload)) {
            $this->publicationDownloads->add($publicationDownload);
            $publicationDownload->setPublication($this);
        }

        return $this;
    }

    public function removePublicationDownload(PublicationDownload $publicationDownload): self
    {
        if ($this->publicationDownloads->removeElement($publicationDownload)) {
            // set the owning side to null (unless already changed)
            if ($publicationDownload->getPublication() === $this) {
                $publicationDownload->setPublication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationPopularity>
     */
    public function getPublicationPopularities(): Collection
    {
        return $this->publicationPopularities;
    }

    public function addPublicationPopularity(PublicationPopularity $publicationPopularity): self
    {
        if (!$this->publicationPopularities->contains($publicationPopularity)) {
            $this->publicationPopularities->add($publicationPopularity);
            $publicationPopularity->setPublication($this);
        }

        return $this;
    }

    public function removePublicationPopularity(PublicationPopularity $publicationPopularity): self
    {
        if ($this->publicationPopularities->removeElement($publicationPopularity)) {
            // set the owning side to null (unless already changed)
            if ($publicationPopularity->getPublication() === $this) {
                $publicationPopularity->setPublication(null);
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
            $publicationComment->setPublication($this);
        }

        return $this;
    }

    public function removePublicationComment(PublicationComment $publicationComment): self
    {
        if ($this->publicationComments->removeElement($publicationComment)) {
            // set the owning side to null (unless already changed)
            if ($publicationComment->getPublication() === $this) {
                $publicationComment->setPublication(null);
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
            $publicationBookmark->setPublication($this);
        }

        return $this;
    }

    public function removePublicationBookmark(PublicationBookmark $publicationBookmark): self
    {
        if ($this->publicationBookmarks->removeElement($publicationBookmark)) {
            // set the owning side to null (unless already changed)
            if ($publicationBookmark->getPublication() === $this) {
                $publicationBookmark->setPublication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationRating>
     */
    public function getPublicationRatings(): Collection
    {
        return $this->publicationRatings;
    }

    public function addPublicationRating(PublicationRating $publicationRating): self
    {
        if (!$this->publicationRatings->contains($publicationRating)) {
            $this->publicationRatings->add($publicationRating);
            $publicationRating->setPublication($this);
        }

        return $this;
    }

    public function removePublicationRating(PublicationRating $publicationRating): self
    {
        if ($this->publicationRatings->removeElement($publicationRating)) {
            // set the owning side to null (unless already changed)
            if ($publicationRating->getPublication() === $this) {
                $publicationRating->setPublication(null);
            }
        }

        return $this;
    }

    public function isFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(?bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * @return Collection<int, PublicationFollow>
     */
    public function getPublicationFollows(): Collection
    {
        return $this->publicationFollows;
    }

    public function addPublicationFollow(PublicationFollow $publicationFollow): self
    {
        if (!$this->publicationFollows->contains($publicationFollow)) {
            $this->publicationFollows->add($publicationFollow);
            $publicationFollow->setPublication($this);
        }

        return $this;
    }

    public function removePublicationFollow(PublicationFollow $publicationFollow): self
    {
        if ($this->publicationFollows->removeElement($publicationFollow)) {
            // set the owning side to null (unless already changed)
            if ($publicationFollow->getPublication() === $this) {
                $publicationFollow->setPublication(null);
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
            $notification->setPublicationFollowAdd($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getPublicationFollowAdd() === $this) {
                $notification->setPublicationFollowAdd(null);
            }
        }

        return $this;
    }

    public function getLastPublishedAt(): ?\DateTimeImmutable
    {
        return $this->lastPublishedAt;
    }

    public function setLastPublishedAt(?\DateTimeImmutable $lastPublishedAt): self
    {
        $this->lastPublishedAt = $lastPublishedAt;

        return $this;
    }
    public function __toString()
    {
        // Si le titre est null, retourner une chaîne vide ou une valeur par défaut
        return $this->title ?? '';
    }

    public function getSalePaper(): ?string
    {
        return $this->sale_paper;
    }

    public function setSalePaper(?string $sale_paper): self
    {
        $this->sale_paper = $sale_paper;

        return $this;
    }

    public function getSaleWeb(): ?string
    {
        return $this->sale_web;
    }

    public function setSaleWeb(?string $sale_web): self
    {
        $this->sale_web = $sale_web;

        return $this;
    }

    public function isSale(): ?bool
    {
        return $this->sale;
    }

    public function setSale(?bool $sale): self
    {
        $this->sale = $sale;

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
            $publicationRead->setPublication($this);
        }

        return $this;
    }

    public function removePublicationRead(PublicationRead $publicationRead): self
    {
        if ($this->publicationReads->removeElement($publicationRead)) {
            // set the owning side to null (unless already changed)
            if ($publicationRead->getPublication() === $this) {
                $publicationRead->setPublication(null);
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
