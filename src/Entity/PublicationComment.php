<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\PublicationCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: PublicationCommentRepository::class)]
class PublicationComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationComments')]
    private ?Publication $publication = null;

    #[ORM\ManyToOne(inversedBy: 'publicationComments')]
    private ?User $User = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $published_at = null;

    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: PublicationCommentLike::class, orphanRemoval: true)]
    private Collection $publicationCommentLikes;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'publicationComments')]
    private ?self $replyTo = null;

    #[ORM\OneToMany(mappedBy: 'replyTo', targetEntity: self::class, orphanRemoval: true)]
    private Collection $publicationComments;

    #[ORM\ManyToOne(inversedBy: 'publicationComments')]
    private ?PublicationChapter $chapter = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $UpdatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $quote = null;

    public function __construct()
    {
        $this->publicationCommentLikes = new ArrayCollection();
        $this->publicationComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

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
        return $this->published_at;
    }

    public function setPublishedAt(?\DateTimeImmutable $published_at): self
    {
        $this->published_at = $published_at;

        return $this;
    }

    /**
     * @return Collection<int, PublicationCommentLike>
     */
    public function getPublicationCommentLikes(): Collection
    {
        return $this->publicationCommentLikes;
    }

    public function addPublicationCommentLike(PublicationCommentLike $publicationCommentLike): self
    {
        if (!$this->publicationCommentLikes->contains($publicationCommentLike)) {
            $this->publicationCommentLikes->add($publicationCommentLike);
            $publicationCommentLike->setComment($this);
        }

        return $this;
    }

    public function removePublicationCommentLike(PublicationCommentLike $publicationCommentLike): self
    {
        if ($this->publicationCommentLikes->removeElement($publicationCommentLike)) {
            // set the owning side to null (unless already changed)
            if ($publicationCommentLike->getComment() === $this) {
                $publicationCommentLike->setComment(null);
            }
        }

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
    public function getPublicationComments(): Collection
    {
        return $this->publicationComments;
    }

    public function addPublicationComment(self $publicationComment): self
    {
        if (!$this->publicationComments->contains($publicationComment)) {
            $this->publicationComments->add($publicationComment);
            $publicationComment->setReplyTo($this);
        }

        return $this;
    }

    public function removePublicationComment(self $publicationComment): self
    {
        if ($this->publicationComments->removeElement($publicationComment)) {
            // set the owning side to null (unless already changed)
            if ($publicationComment->getReplyTo() === $this) {
                $publicationComment->setReplyTo(null);
            }
        }

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $UpdatedAt): self
    {
        $this->UpdatedAt = $UpdatedAt;

        return $this;
    }

    public function getQuote(): ?string
    {
        return $this->quote;
    }

    public function setQuote(?string $quote): self
    {
        $this->quote = $quote;

        return $this;
    }
}