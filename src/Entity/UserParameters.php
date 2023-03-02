<?php

namespace App\Entity;

use App\Repository\UserParametersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserParametersRepository::class)]
class UserParameters
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userParameters', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $darkmode = null;

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

    public function isDarkmode(): ?bool
    {
        return $this->darkmode;
    }

    public function setDarkmode(?bool $darkmode): self
    {
        $this->darkmode = $darkmode;

        return $this;
    }
}
