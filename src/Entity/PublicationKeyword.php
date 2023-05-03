<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\PublicationKeywordRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: PublicationKeywordRepository::class)]
class PublicationKeyword
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $keyword = null;

    #[ORM\ManyToMany(targetEntity: Publication::class, inversedBy: 'publicationKeywords')]
    private Collection $publication;

    #[ORM\Column]
    private ?int $count = null;

    public function __construct()
    {
        $this->publication = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(?string $keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * @return Collection<int, Publication>
     */
    public function getPublication(): Collection
    {
        return $this->publication;
    }

    public function addPublication(Publication $publication): self
    {
        if (!$this->publication->contains($publication)) {
            $this->publication->add($publication);
        }

        return $this;
    }

    public function removePublication(Publication $publication): self
    {
        $this->publication->removeElement($publication);

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }
}
