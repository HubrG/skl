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

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationChapter::class)]
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

    public function __construct()
    {
        $this->publicationChapters = new ArrayCollection();
        $this->publicationKeywords = new ArrayCollection();
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
}
