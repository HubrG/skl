<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PublicationAnnotationRepository;

#[ORM\Entity(repositoryClass: PublicationAnnotationRepository::class)]
class PublicationAnnotation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationAnnotations')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $AnnotationClass = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;


    #[ORM\ManyToOne(inversedBy: 'publicationAnnotations')]
    private ?PublicationChapter $chapter = null;

    #[ORM\Column(nullable: true)]
    private ?int $color = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content_plain = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $mode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    private ?int $reviewType = null;

    #[ORM\ManyToOne(inversedBy: 'publicationAnnotations')]
    private ?PublicationChapterVersioning $version = null;

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

    public function getAnnotationClass(): ?string
    {
        return $this->AnnotationClass;
    }

    public function setAnnotationClass(?string $AnnotationClass): self
    {
        $this->AnnotationClass = $AnnotationClass;

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


    public function getChapter(): ?PublicationChapter
    {
        return $this->chapter;
    }

    public function setChapter(?PublicationChapter $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getColor(): ?int
    {
        return $this->color;
    }

    public function setColor(?int $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getContentPlain(): ?string
    {
        return $this->content_plain;
    }

    public function setContentPlain(?string $content_plain): self
    {
        $this->content_plain = $content_plain;

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

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(?int $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getReviewType(): ?int
    {
        return $this->reviewType;
    }

    public function setReviewType(?int $reviewType): self
    {
        $this->reviewType = $reviewType;

        return $this;
    }

    public function getVersion(): ?PublicationChapterVersioning
    {
        return $this->version;
    }

    public function setVersion(?PublicationChapterVersioning $version): self
    {
        $this->version = $version;

        return $this;
    }
}