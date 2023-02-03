<?php

namespace App\Entity;

use App\Repository\PublicationChapterViewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationChapterViewRepository::class)]
class PublicationChapterView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterViews')]
    private ?user $user = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterViews')]
    private ?PublicationChapter $chapter = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $view_date = null;

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

    public function getViewDate(): ?\DateTimeInterface
    {
        return $this->view_date;
    }

    public function setViewDate(?\DateTimeInterface $view_date): self
    {
        $this->view_date = $view_date;

        return $this;
    }
}
