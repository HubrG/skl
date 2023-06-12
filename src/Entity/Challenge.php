<?php

namespace App\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ChallengeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
class Challenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'challenges')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $dateStart = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $dateEnd = null;

    #[ORM\ManyToOne(inversedBy: 'challenges')]
    private ?PublicationCategory $constrainCategory = null;

    #[ORM\Column(nullable: true)]
    private ?int $constrainMinTime = null;
    #[ORM\Column(nullable: true)]
    private ?int $constrainMaxTime = null;

    #[ORM\Column(nullable: true)]
    private ?int $constrainMaxWords = null;

    #[ORM\Column(nullable: true)]
    private ?int $constrainMinWords = null;

    #[ORM\Column(nullable: true)]
    private ?int $constrainMinLetters = null;

    #[ORM\Column(nullable: true)]
    private ?int $constrainMaxLetters = null;
    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'challenge', targetEntity: ChallengeMessage::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $challengeMessages;

    #[ORM\OneToMany(mappedBy: 'challenge', targetEntity: Publication::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publications;

    #[ORM\Column]
    private ?bool $contest = null;

    public function __construct()
    {
        $this->challengeMessages = new ArrayCollection();
        $this->publications = new ArrayCollection();
    }



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

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getConstrainCategory(): ?PublicationCategory
    {
        return $this->constrainCategory;
    }

    public function setConstrainCategory(?PublicationCategory $constrainCategory): self
    {
        $this->constrainCategory = $constrainCategory;

        return $this;
    }

    public function getConstrainMinTime(): ?int
    {
        return $this->constrainMinTime;
    }

    public function setConstrainMinTime(?int $constrainMinTime): self
    {
        $this->constrainMinTime = $constrainMinTime;

        return $this;
    }

    public function getConstrainMaxTime(): ?int
    {
        return $this->constrainMaxTime;
    }

    public function setConstrainMaxTime(?int $constrainMaxTime): self
    {
        $this->constrainMaxTime = $constrainMaxTime;

        return $this;
    }

    public function getDateStart(): ?DateTime
    {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTimeInterface $dateStart): self
    {

        $this->dateStart = $dateStart;


        return $this;
    }


    public function getDateEnd(): ?DateTime
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?DateTime $dateEnd): self
    {

        $this->dateEnd = $dateEnd;


        return $this;
    }

    public function getConstrainMaxWords(): ?int
    {
        return $this->constrainMaxWords;
    }

    public function setConstrainMaxWords(?int $constrainMaxWords): self
    {
        $this->constrainMaxWords = $constrainMaxWords;

        return $this;
    }

    public function getConstrainMinWords(): ?int
    {
        return $this->constrainMinWords;
    }

    public function setConstrainMinWords(?int $constrainMinWords): self
    {
        $this->constrainMinWords = $constrainMinWords;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getConstrainMinLetters(): ?int
    {
        return $this->constrainMinLetters;
    }

    public function setConstrainMinLetters(?int $constrainMinLetters): self
    {
        $this->constrainMinLetters = $constrainMinLetters;

        return $this;
    }

    public function getConstrainMaxLetters(): ?int
    {
        return $this->constrainMaxLetters;
    }

    public function setConstrainMaxLetters(int $constrainMaxLetters): self
    {
        $this->constrainMaxLetters = $constrainMaxLetters;

        return $this;
    }

    /**
     * @return Collection<int, ChallengeMessage>
     */
    public function getChallengeMessages(): Collection
    {
        return $this->challengeMessages;
    }

    public function addChallengeMessage(ChallengeMessage $challengeMessage): self
    {
        if (!$this->challengeMessages->contains($challengeMessage)) {
            $this->challengeMessages->add($challengeMessage);
            $challengeMessage->setChallenge($this);
        }

        return $this;
    }

    public function removeChallengeMessage(ChallengeMessage $challengeMessage): self
    {
        if ($this->challengeMessages->removeElement($challengeMessage)) {
            // set the owning side to null (unless already changed)
            if ($challengeMessage->getChallenge() === $this) {
                $challengeMessage->setChallenge(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Publication>
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): self
    {
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);
            $publication->setChallenge($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): self
    {
        if ($this->publications->removeElement($publication)) {
            // set the owning side to null (unless already changed)
            if ($publication->getChallenge() === $this) {
                $publication->setChallenge(null);
            }
        }

        return $this;
    }

    public function isContest(): ?bool
    {
        return $this->contest;
    }

    public function setContest(bool $contest): self
    {
        $this->contest = $contest;

        return $this;
    }
}
