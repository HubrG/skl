<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserParametersRepository;

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

    #[ORM\Column(nullable: true)]
    private ?bool $grid_show = null;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_1_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_1_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_2_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_2_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_3_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_3_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_4_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_4_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_5_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_5_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_6_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_6_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_7_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_7_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_8_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_8_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_9_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_9_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_10_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_10_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_11_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_11_web = true;
    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_12_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_12_web = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_13_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_13_web = true;
    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_14_mail = true;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $notif_14_web = true;


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

    public function isGridShow(): ?bool
    {
        return $this->grid_show;
    }

    public function setGridShow(?bool $grid_show): self
    {
        $this->grid_show = $grid_show;

        return $this;
    }

    public function isNotif1Mail(): ?bool
    {
        return $this->notif_1_mail;
    }

    public function setNotif1Mail(?bool $notif_1_mail): self
    {
        $this->notif_1_mail = $notif_1_mail;

        return $this;
    }

    public function isNotif1Web(): ?bool
    {
        return $this->notif_1_web;
    }

    public function setNotif1Web(?bool $notif_1_web): self
    {
        $this->notif_1_web = $notif_1_web;

        return $this;
    }

    public function isNotif2Mail(): ?bool
    {
        return $this->notif_2_mail;
    }

    public function setNotif2Mail(?bool $notif_2_mail): self
    {
        $this->notif_2_mail = $notif_2_mail;

        return $this;
    }

    public function isNotif2Web(): ?bool
    {
        return $this->notif_2_web;
    }

    public function setNotif2Web(?bool $notif_2_web): self
    {
        $this->notif_2_web = $notif_2_web;

        return $this;
    }

    public function isNotif3Mail(): ?bool
    {
        return $this->notif_3_mail;
    }

    public function setNotif3Mail(?bool $notif_3_mail): self
    {
        $this->notif_3_mail = $notif_3_mail;

        return $this;
    }

    public function isNotif3Web(): ?bool
    {
        return $this->notif_3_web;
    }

    public function setNotif3Web(?bool $notif_3_web): self
    {
        $this->notif_3_web = $notif_3_web;

        return $this;
    }

    public function isNotif4Mail(): ?bool
    {
        return $this->notif_4_mail;
    }

    public function setNotif4Mail(?bool $notif_4_mail): self
    {
        $this->notif_4_mail = $notif_4_mail;

        return $this;
    }

    public function isNotif4Web(): ?bool
    {
        return $this->notif_4_web;
    }

    public function setNotif4Web(?bool $notif_4_web): self
    {
        $this->notif_4_web = $notif_4_web;

        return $this;
    }

    public function isNotif5Mail(): ?bool
    {
        return $this->notif_5_mail;
    }

    public function setNotif5Mail(?bool $notif_5_mail): self
    {
        $this->notif_5_mail = $notif_5_mail;

        return $this;
    }

    public function isNotif5Web(): ?bool
    {
        return $this->notif_5_web;
    }

    public function setNotif5Web(?bool $notif_5_web): self
    {
        $this->notif_5_web = $notif_5_web;

        return $this;
    }

    public function isNotif6Mail(): ?bool
    {
        return $this->notif_6_mail;
    }

    public function setNotif6Mail(?bool $notif_6_mail): self
    {
        $this->notif_6_mail = $notif_6_mail;

        return $this;
    }

    public function isNotif6Web(): ?bool
    {
        return $this->notif_6_web;
    }

    public function setNotif6Web(?bool $notif_6_web): self
    {
        $this->notif_6_web = $notif_6_web;

        return $this;
    }

    public function isNotif7Mail(): ?bool
    {
        return $this->notif_7_mail;
    }

    public function setNotif7Mail(?bool $notif_7_mail): self
    {
        $this->notif_7_mail = $notif_7_mail;

        return $this;
    }

    public function isNotif7Web(): ?bool
    {
        return $this->notif_7_web;
    }

    public function setNotif7Web(?bool $notif_7_web): self
    {
        $this->notif_7_web = $notif_7_web;

        return $this;
    }

    public function isNotif8Mail(): ?bool
    {
        return $this->notif_8_mail;
    }

    public function setNotif8Mail(?bool $notif_8_mail): self
    {
        $this->notif_8_mail = $notif_8_mail;

        return $this;
    }

    public function isNotif8Web(): ?bool
    {
        return $this->notif_8_web;
    }

    public function setNotif8Web(?bool $notif_8_web): self
    {
        $this->notif_8_web = $notif_8_web;

        return $this;
    }

    public function isNotif9Mail(): ?bool
    {
        return $this->notif_9_mail;
    }

    public function setNotif9Mail(?bool $notif_9_mail): self
    {
        $this->notif_9_mail = $notif_9_mail;

        return $this;
    }

    public function isNotif9Web(): ?bool
    {
        return $this->notif_9_web;
    }

    public function setNotif9Web(?bool $notif_9_web): self
    {
        $this->notif_9_web = $notif_9_web;

        return $this;
    }
    public function isNotif10Mail(): ?bool
    {
        return $this->notif_10_mail;
    }

    public function setNotif10Mail(?bool $notif_10_mail): self
    {
        $this->notif_10_mail = $notif_10_mail;

        return $this;
    }

    public function isNotif10Web(): ?bool
    {
        return $this->notif_10_web;
    }

    public function setNotif10Web(?bool $notif_10_web): self
    {
        $this->notif_10_web = $notif_10_web;

        return $this;
    }
    public function isNotif11Mail(): ?bool
    {
        return $this->notif_11_mail;
    }

    public function setNotif11Mail(?bool $notif_11_mail): self
    {
        $this->notif_11_mail = $notif_11_mail;

        return $this;
    }

    public function isNotif11Web(): ?bool
    {
        return $this->notif_11_web;
    }

    public function setNotif11Web(?bool $notif_11_web): self
    {
        $this->notif_11_web = $notif_11_web;

        return $this;
    }
    public function isNotif12Mail(): ?bool
    {
        return $this->notif_12_mail;
    }

    public function setNotif12Mail(?bool $notif_12_mail): self
    {
        $this->notif_12_mail = $notif_12_mail;

        return $this;
    }

    public function isNotif12Web(): ?bool
    {
        return $this->notif_12_web;
    }

    public function setNotif12Web(?bool $notif_12_web): self
    {
        $this->notif_12_web = $notif_12_web;

        return $this;
    }
    public function isNotif13Mail(): ?bool
    {
        return $this->notif_13_mail;
    }

    public function setNotif13Mail(?bool $notif_13_mail): self
    {
        $this->notif_13_mail = $notif_13_mail;

        return $this;
    }

    public function isNotif13Web(): ?bool
    {
        return $this->notif_13_web;
    }

    public function setNotif13Web(?bool $notif_13_web): self
    {
        $this->notif_13_web = $notif_13_web;

        return $this;
    }
    public function isNotif14Mail(): ?bool
    {
        return $this->notif_14_mail;
    }

    public function setNotif14Mail(?bool $notif_14_mail): self
    {
        $this->notif_14_mail = $notif_14_mail;

        return $this;
    }

    public function isNotif14Web(): ?bool
    {
        return $this->notif_14_web;
    }

    public function setNotif14Web(?bool $notif_14_web): self
    {
        $this->notif_14_web = $notif_14_web;

        return $this;
    }
}
