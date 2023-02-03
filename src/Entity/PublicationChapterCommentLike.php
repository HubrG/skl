<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\PublicationChapterCommentLikeRepository;

#[ORM\Entity(repositoryClass: PublicationChapterCommentLikeRepository::class)]
class PublicationChapterCommentLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\ManyToOne(inversedBy: 'publicationChapterCommentLikes')]
    private ?PublicationChapterComment $comment = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterCommentLikes')]
    private ?user $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $like_date = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, user>
     */



    public function getComment(): ?PublicationChapterComment
    {
        return $this->comment;
    }

    public function setComment(?PublicationChapterComment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLikeDate(): ?\DateTimeInterface
    {
        return $this->like_date;
    }

    public function setLikeDate(?\DateTimeInterface $like_date): self
    {
        $this->like_date = $like_date;

        return $this;
    }
}
