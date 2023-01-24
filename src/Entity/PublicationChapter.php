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

    #[ORM\OneToMany(mappedBy: 'chapter', cascade: ['remove'], targetEntity: PublicationChapterVersioning::class)]
    private Collection $publicationChapterVersionings;

    public function __construct()
    {
        $this->publicationChapterVersionings = new ArrayCollection();
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
}
