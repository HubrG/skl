<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\PublicationChapterCommentRepository;

#[ORM\Entity(repositoryClass: PublicationChapterCommentRepository::class)]
class PublicationChapterComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterComments')]
    private ?PublicationChapter $chapter = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterComments')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publish_date = null;

    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: PublicationChapterCommentLike::class, orphanRemoval: true)]
    private Collection $publicationChapterCommentLikes;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $quote = null;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPublishDate(): ?\DateTimeInterface
    {
        return $this->publish_date;
    }

    public function setPublishDate(?\DateTimeInterface $publish_date): self
    {
        $this->publish_date = $publish_date;

        return $this;
    }

    /**
     * @return Collection<int, PublicationChapterCommentLike>
     */
    public function getPublicationChapterCommentLikes(): Collection
    {
        return $this->publicationChapterCommentLikes;
    }

    public function addPublicationChapterCommentLike(PublicationChapterCommentLike $publicationChapterCommentLike): self
    {
        if (!$this->publicationChapterCommentLikes->contains($publicationChapterCommentLike)) {
            $this->publicationChapterCommentLikes->add($publicationChapterCommentLike);
            $publicationChapterCommentLike->setComment($this);
        }

        return $this;
    }

    public function removePublicationChapterCommentLike(PublicationChapterCommentLike $publicationChapterCommentLike): self
    {
        if ($this->publicationChapterCommentLikes->removeElement($publicationChapterCommentLike)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterCommentLike->getComment() === $this) {
                $publicationChapterCommentLike->setComment(null);
            }
        }

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
