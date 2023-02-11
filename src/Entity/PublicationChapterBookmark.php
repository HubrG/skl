<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PublicationChapterBookmarkRepository;

#[ORM\Entity(repositoryClass: PublicationChapterBookmarkRepository::class)]
class PublicationChapterBookmark
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterBookmarks')]
    private ?user $user = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterBookmarks')]
    private ?PublicationChapter $chapter = null;

    public function getId(): ?int
    {
        return $this->id;
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
