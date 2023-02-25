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

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationChapterComment::class, orphanRemoval: true)]
    private Collection $publicationChapterComments;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationChapterView::class, orphanRemoval: true)]
    private Collection $publicationChapterViews;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationChapterNote::class, orphanRemoval: true)]
    private Collection $publicationChapterNotes;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationChapterLike::class, orphanRemoval: true)]
    private Collection $publicationChapterLikes;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationChapterBookmark::class, orphanRemoval: true)]
    private Collection $publicationChapterBookmarks;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationComment::class)]
    private Collection $publicationComments;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PublicationBookmark::class)]
    private Collection $publicationBookmarks;

    public function __construct()
    {
        $this->publicationChapterVersionings = new ArrayCollection();
        $this->publicationChapterComments = new ArrayCollection();
        $this->publicationChapterViews = new ArrayCollection();
        $this->publicationChapterNotes = new ArrayCollection();
        $this->publicationChapterLikes = new ArrayCollection();
        $this->publicationChapterBookmarks = new ArrayCollection();
        $this->publicationComments = new ArrayCollection();
        $this->publicationBookmarks = new ArrayCollection();
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
     * @return Collection<int, PublicationChapterComment>
     */
    public function getPublicationChapterComments(): Collection
    {
        return $this->publicationChapterComments;
    }

    public function addPublicationChapterComment(PublicationChapterComment $publicationChapterComment): self
    {
        if (!$this->publicationChapterComments->contains($publicationChapterComment)) {
            $this->publicationChapterComments->add($publicationChapterComment);
            $publicationChapterComment->setChapter($this);
        }

        return $this;
    }

    public function removePublicationChapterComment(PublicationChapterComment $publicationChapterComment): self
    {
        if ($this->publicationChapterComments->removeElement($publicationChapterComment)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterComment->getChapter() === $this) {
                $publicationChapterComment->setChapter(null);
            }
        }

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
     * @return Collection<int, PublicationChapterBookmark>
     */
    public function getPublicationChapterBookmarks(): Collection
    {
        return $this->publicationChapterBookmarks;
    }

    public function addPublicationChapterBookmark(PublicationChapterBookmark $publicationChapterBookmark): self
    {
        if (!$this->publicationChapterBookmarks->contains($publicationChapterBookmark)) {
            $this->publicationChapterBookmarks->add($publicationChapterBookmark);
            $publicationChapterBookmark->setChapter($this);
        }

        return $this;
    }

    public function removePublicationChapterBookmark(PublicationChapterBookmark $publicationChapterBookmark): self
    {
        if ($this->publicationChapterBookmarks->removeElement($publicationChapterBookmark)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterBookmark->getChapter() === $this) {
                $publicationChapterBookmark->setChapter(null);
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
}
