<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\InboxGroupRepository;

#[ORM\Entity(repositoryClass: InboxGroupRepository::class)]
class InboxGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true,  unique: true)]
    private ?string $room = null;

    #[ORM\OneToMany(mappedBy: 'grouped', targetEntity: InboxGroupMember::class)]
    private Collection $inboxGroupMembers;

    #[ORM\OneToMany(mappedBy: 'grouped', targetEntity: Inbox::class)]
    private Collection $inboxes;

    public function __construct()
    {
        $this->inboxGroupMembers = new ArrayCollection();
        $this->inboxes = new ArrayCollection();
    }




    public function getId(): ?int
    {
        return $this->id;
    }



    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(?string $room): self
    {
        $this->room = $room;

        return $this;
    }

    /**
     * @return Collection<int, InboxGroupMember>
     */
    public function getInboxGroupMembers(): Collection
    {
        return $this->inboxGroupMembers;
    }

    public function addInboxGroupMember(InboxGroupMember $inboxGroupMember): self
    {
        if (!$this->inboxGroupMembers->contains($inboxGroupMember)) {
            $this->inboxGroupMembers->add($inboxGroupMember);
            $inboxGroupMember->setGrouped($this);
        }

        return $this;
    }

    public function removeInboxGroupMember(InboxGroupMember $inboxGroupMember): self
    {
        if ($this->inboxGroupMembers->removeElement($inboxGroupMember)) {
            // set the owning side to null (unless already changed)
            if ($inboxGroupMember->getGrouped() === $this) {
                $inboxGroupMember->setGrouped(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Inbox>
     */
    public function getInboxes(): Collection
    {
        return $this->inboxes;
    }

    public function addInbox(Inbox $inbox): self
    {
        if (!$this->inboxes->contains($inbox)) {
            $this->inboxes->add($inbox);
            $inbox->setGrouped($this);
        }

        return $this;
    }

    public function removeInbox(Inbox $inbox): self
    {
        if ($this->inboxes->removeElement($inbox)) {
            // set the owning side to null (unless already changed)
            if ($inbox->getGrouped() === $this) {
                $inbox->setGrouped(null);
            }
        }

        return $this;
    }
}
