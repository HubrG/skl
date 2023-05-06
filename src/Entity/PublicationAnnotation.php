<?php

namespace App\Entity;

use App\Repository\PublicationAnnotationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(nullable: true)]
    private ?int $startOffset = null;

    #[ORM\Column(nullable: true)]
    private ?int $endOffset = null;

    #[ORM\ManyToOne(inversedBy: 'publicationAnnotations')]
    private ?PublicationChapter $chapter = null;

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

    public function getStartOffset(): ?int
    {
        return $this->startOffset;
    }

    public function setStartOffset(?int $startOffset): self
    {
        $this->startOffset = $startOffset;

        return $this;
    }

    public function getEndOffset(): ?int
    {
        return $this->endOffset;
    }

    public function setEndOffset(?int $endOffset): self
    {
        $this->endOffset = $endOffset;

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
}
