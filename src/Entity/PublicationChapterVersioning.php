<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\PublicationChapterVersioningRepository;

#[ORM\Entity(repositoryClass: PublicationChapterVersioningRepository::class)]
class PublicationChapterVersioning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterVersionings')]
    private ?PublicationChapter $chapter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created = null;

    #[ORM\OneToMany(mappedBy: 'version', targetEntity: PublicationAnnotation::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationAnnotations;

    public function __construct()
    {
        $this->publicationAnnotations = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

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

    public function setCreated(?\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return Collection<int, PublicationAnnotation>
     */
    public function getPublicationAnnotations(): Collection
    {
        return $this->publicationAnnotations;
    }

    public function addPublicationAnnotation(PublicationAnnotation $publicationAnnotation): self
    {
        if (!$this->publicationAnnotations->contains($publicationAnnotation)) {
            $this->publicationAnnotations->add($publicationAnnotation);
            $publicationAnnotation->setVersion($this);
        }

        return $this;
    }

    public function removePublicationAnnotation(PublicationAnnotation $publicationAnnotation): self
    {
        if ($this->publicationAnnotations->removeElement($publicationAnnotation)) {
            // set the owning side to null (unless already changed)
            if ($publicationAnnotation->getVersion() === $this) {
                $publicationAnnotation->setVersion(null);
            }
        }

        return $this;
    }
}
