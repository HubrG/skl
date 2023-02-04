<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PublicationChapterNoteRepository;

#[ORM\Entity(repositoryClass: PublicationChapterNoteRepository::class)]
class PublicationChapterNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterNotes')]
    private ?user $user = null;

    #[ORM\ManyToOne(inversedBy: 'publicationChapterNotes')]
    private ?PublicationChapter $chapter = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $selection = null;

    #[ORM\Column(nullable: true)]
    private ?int $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $add_date = null;

    #[ORM\Column(nullable: true)]
    private ?int $start = null;

    #[ORM\Column(nullable: true)]
    private ?int $end = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $surround = null;

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

    public function getSelection(): ?string
    {
        return $this->selection;
    }

    public function setSelection(?string $selection): self
    {
        $this->selection = $selection;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAddDate(): ?\DateTimeInterface
    {
        return $this->add_date;
    }

    public function setAddDate(?\DateTimeInterface $add_date): self
    {
        $this->add_date = $add_date;

        return $this;
    }

    public function getStart(): ?int
    {
        return $this->start;
    }

    public function setStart(?int $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?int
    {
        return $this->end;
    }

    public function setEnd(?int $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getsurround(): ?string
    {
        return $this->surround;
    }

    public function setsurround(?string $surround): self
    {
        $this->surround = $surround;

        return $this;
    }
}
