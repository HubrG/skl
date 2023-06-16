<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserFollowRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà enregistrée')]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d\'utilisateur existe déjà')]
#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['username' => 'partial'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["user:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "Vous devez renseigner votre adresse email")]
    #[Assert\Email(message: "Cette adresse email semble incorrecte")]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    #[Assert\NotBlank(message: "Vous devez renseigner un nom d'utilisateur")]
    #[Groups(["user:read"])]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["user:read"])]
    private ?string $nickname = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $about = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twitter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebook = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["user:read"])]
    private ?string $profil_picture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birth = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profil_background = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Publication::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publications;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $join_date = null;


    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationChapterView::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationChapterViews;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationChapterNote::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationChapterNotes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationChapterLike::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationChapterLikes;


    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationDownload::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationDownloads;

    /**
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @Assert\Length(max=4096)
     */
    private $oldPassword;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: PublicationComment::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationComments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationBookmark::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationBookmarks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationCommentLike::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationCommentLikes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationRating::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationRatings;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationBookmarkCollection::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationBookmarkCollections;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'from_user', targetEntity: Notification::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $UserFrom;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserParameters $userParameters = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationFollow::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationFollows;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customer_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $provider_name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $googleId;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationRead::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationReads;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ForumTopic::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $forumTopics;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ForumMessage::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $forumMessages;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ForumTopicRead::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $forumTopicReads;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ForumTopicView::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $forumTopicViews;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationAnnotation::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationAnnotations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Inbox::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $inboxes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: InboxGroupMember::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $inboxGroupMembers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ForumMessageLike::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $forumMessageLikes;

    #[ORM\OneToMany(mappedBy: 'fromUser', targetEntity: UserFollow::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $userFollows;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationAccess::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationAccesses;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Challenge::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $challenges;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationSupport::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationSupports;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ChallengeMessage::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $challengeMessages;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ChallengeMessageLike::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $challengeMessageLikes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PublicationAnnotationReply::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $publicationAnnotationReplies;
    public function __construct()
    {
        $this->publications = new ArrayCollection();
        $this->publicationChapterViews = new ArrayCollection();
        $this->publicationChapterNotes = new ArrayCollection();
        $this->publicationChapterLikes = new ArrayCollection();
        $this->publicationDownloads = new ArrayCollection();
        $this->publicationComments = new ArrayCollection();
        $this->publicationBookmarks = new ArrayCollection();
        $this->publicationCommentLikes = new ArrayCollection();
        $this->publicationRatings = new ArrayCollection();
        $this->publicationBookmarkCollections = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->UserFrom = new ArrayCollection();
        $this->publicationFollows = new ArrayCollection();
        $this->publicationReads = new ArrayCollection();
        $this->forumTopics = new ArrayCollection();
        $this->forumMessages = new ArrayCollection();
        $this->forumTopicReads = new ArrayCollection();
        $this->forumTopicViews = new ArrayCollection();
        $this->publicationAnnotations = new ArrayCollection();
        $this->inboxes = new ArrayCollection();
        $this->inboxGroupMembers = new ArrayCollection();
        $this->forumMessageLikes = new ArrayCollection();
        $this->userFollows = new ArrayCollection();
        $this->publicationAccesses = new ArrayCollection();
        $this->challenges = new ArrayCollection();
        $this->publicationSupports = new ArrayCollection();
        $this->challengeMessages = new ArrayCollection();
        $this->challengeMessageLikes = new ArrayCollection();
        $this->publicationAnnotationReplies = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        //$this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = $about;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getProfilPicture(): ?string
    {
        return $this->profil_picture;
    }

    public function setProfilPicture(?string $profil_picture): self
    {
        $this->profil_picture = $profil_picture;

        return $this;
    }

    public function getBirth(): ?\DateTimeInterface
    {
        return $this->birth;
    }

    public function setBirth(?\DateTimeInterface $birth): self
    {
        $this->birth = $birth;

        return $this;
    }

    public function getProfilBackground(): ?string
    {
        return $this->profil_background;
    }

    public function setProfilBackground(string $profil_background): self
    {
        $this->profil_background = $profil_background;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

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
            $publication->setUser($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): self
    {
        if ($this->publications->removeElement($publication)) {
            // set the owning side to null (unless already changed)
            if ($publication->getUser() === $this) {
                $publication->setUser(null);
            }
        }

        return $this;
    }

    public function getJoinDate(): ?\DateTimeInterface
    {
        return $this->join_date;
    }

    public function setJoinDate(\DateTimeInterface $join_date): self
    {
        $this->join_date = $join_date;

        return $this;
    }


    /**
     * @return Collection<int, PublicationChapterView>
     */
    public function getPublicationChapterViews(): Collection
    {
        return $this->publicationChapterViews;
    }

    public function addPublicationChapterView(PublicationChapterView $publicationChapterView): self
    {
        if (!$this->publicationChapterViews->contains($publicationChapterView)) {
            $this->publicationChapterViews->add($publicationChapterView);
            $publicationChapterView->setUser($this);
        }

        return $this;
    }

    public function removePublicationChapterView(PublicationChapterView $publicationChapterView): self
    {
        if ($this->publicationChapterViews->removeElement($publicationChapterView)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterView->getUser() === $this) {
                $publicationChapterView->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationChapterNote>
     */
    public function getPublicationChapterNotes(): Collection
    {
        return $this->publicationChapterNotes;
    }

    public function addPublicationChapterNote(PublicationChapterNote $publicationChapterNote): self
    {
        if (!$this->publicationChapterNotes->contains($publicationChapterNote)) {
            $this->publicationChapterNotes->add($publicationChapterNote);
            $publicationChapterNote->setUser($this);
        }

        return $this;
    }

    public function removePublicationChapterNote(PublicationChapterNote $publicationChapterNote): self
    {
        if ($this->publicationChapterNotes->removeElement($publicationChapterNote)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterNote->getUser() === $this) {
                $publicationChapterNote->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationChapterLike>
     */
    public function getPublicationChapterLikes(): Collection
    {
        return $this->publicationChapterLikes;
    }

    public function addPublicationChapterLike(PublicationChapterLike $publicationChapterLike): self
    {
        if (!$this->publicationChapterLikes->contains($publicationChapterLike)) {
            $this->publicationChapterLikes->add($publicationChapterLike);
            $publicationChapterLike->setUser($this);
        }

        return $this;
    }

    public function removePublicationChapterLike(PublicationChapterLike $publicationChapterLike): self
    {
        if ($this->publicationChapterLikes->removeElement($publicationChapterLike)) {
            // set the owning side to null (unless already changed)
            if ($publicationChapterLike->getUser() === $this) {
                $publicationChapterLike->setUser(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, PublicationDownload>
     */
    public function getPublicationDownloads(): Collection
    {
        return $this->publicationDownloads;
    }

    public function addPublicationDownload(PublicationDownload $publicationDownload): self
    {
        if (!$this->publicationDownloads->contains($publicationDownload)) {
            $this->publicationDownloads->add($publicationDownload);
            $publicationDownload->setUser($this);
        }

        return $this;
    }

    public function removePublicationDownload(PublicationDownload $publicationDownload): self
    {
        if ($this->publicationDownloads->removeElement($publicationDownload)) {
            // set the owning side to null (unless already changed)
            if ($publicationDownload->getUser() === $this) {
                $publicationDownload->setUser(null);
            }
        }

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram;

        return $this;
    }
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;
        return $this;
    }

    /**
     * @return Collection<int, PublicationComment>
     */
    public function getPublicationComments(): Collection
    {
        return $this->publicationComments;
    }

    public function addPublicationComment(PublicationComment $publicationComment): self
    {
        if (!$this->publicationComments->contains($publicationComment)) {
            $this->publicationComments->add($publicationComment);
            $publicationComment->setUser($this);
        }

        return $this;
    }

    public function removePublicationComment(PublicationComment $publicationComment): self
    {
        if ($this->publicationComments->removeElement($publicationComment)) {
            // set the owning side to null (unless already changed)
            if ($publicationComment->getUser() === $this) {
                $publicationComment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationBookmark>
     */
    public function getPublicationBookmarks(): Collection
    {
        return $this->publicationBookmarks;
    }

    public function addPublicationBookmark(PublicationBookmark $publicationBookmark): self
    {
        if (!$this->publicationBookmarks->contains($publicationBookmark)) {
            $this->publicationBookmarks->add($publicationBookmark);
            $publicationBookmark->setUser($this);
        }

        return $this;
    }

    public function removePublicationBookmark(PublicationBookmark $publicationBookmark): self
    {
        if ($this->publicationBookmarks->removeElement($publicationBookmark)) {
            // set the owning side to null (unless already changed)
            if ($publicationBookmark->getUser() === $this) {
                $publicationBookmark->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationCommentLike>
     */
    public function getPublicationCommentLikes(): Collection
    {
        return $this->publicationCommentLikes;
    }

    public function addPublicationCommentLike(PublicationCommentLike $publicationCommentLike): self
    {
        if (!$this->publicationCommentLikes->contains($publicationCommentLike)) {
            $this->publicationCommentLikes->add($publicationCommentLike);
            $publicationCommentLike->setUser($this);
        }

        return $this;
    }

    public function removePublicationCommentLike(PublicationCommentLike $publicationCommentLike): self
    {
        if ($this->publicationCommentLikes->removeElement($publicationCommentLike)) {
            // set the owning side to null (unless already changed)
            if ($publicationCommentLike->getUser() === $this) {
                $publicationCommentLike->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationRating>
     */
    public function getPublicationRatings(): Collection
    {
        return $this->publicationRatings;
    }

    public function addPublicationRating(PublicationRating $publicationRating): self
    {
        if (!$this->publicationRatings->contains($publicationRating)) {
            $this->publicationRatings->add($publicationRating);
            $publicationRating->setUser($this);
        }

        return $this;
    }

    public function removePublicationRating(PublicationRating $publicationRating): self
    {
        if ($this->publicationRatings->removeElement($publicationRating)) {
            // set the owning side to null (unless already changed)
            if ($publicationRating->getUser() === $this) {
                $publicationRating->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationBookmarkCollection>
     */
    public function getPublicationBookmarkCollections(): Collection
    {
        return $this->publicationBookmarkCollections;
    }

    public function addPublicationBookmarkCollection(PublicationBookmarkCollection $publicationBookmarkCollection): self
    {
        if (!$this->publicationBookmarkCollections->contains($publicationBookmarkCollection)) {
            $this->publicationBookmarkCollections->add($publicationBookmarkCollection);
            $publicationBookmarkCollection->setUser($this);
        }

        return $this;
    }

    public function removePublicationBookmarkCollection(PublicationBookmarkCollection $publicationBookmarkCollection): self
    {
        if ($this->publicationBookmarkCollections->removeElement($publicationBookmarkCollection)) {
            // set the owning side to null (unless already changed)
            if ($publicationBookmarkCollection->getUser() === $this) {
                $publicationBookmarkCollection->setUser(null);
            }
        }

        return $this;
    }

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getUserFrom(): Collection
    {
        return $this->UserFrom;
    }

    public function addUserFrom(Notification $userFrom): self
    {
        if (!$this->UserFrom->contains($userFrom)) {
            $this->UserFrom->add($userFrom);
            $userFrom->setFromUser($this);
        }

        return $this;
    }

    public function removeUserFrom(Notification $userFrom): self
    {
        if ($this->UserFrom->removeElement($userFrom)) {
            // set the owning side to null (unless already changed)
            if ($userFrom->getFromUser() === $this) {
                $userFrom->setFromUser(null);
            }
        }

        return $this;
    }

    public function getUserParameters(): ?UserParameters
    {
        return $this->userParameters;
    }

    public function setUserParameters(?UserParameters $userParameters): self
    {
        // unset the owning side of the relation if necessary
        if ($userParameters === null && $this->userParameters !== null) {
            $this->userParameters->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($userParameters !== null && $userParameters->getUser() !== $this) {
            $userParameters->setUser($this);
        }

        $this->userParameters = $userParameters;

        return $this;
    }

    /**
     * @return Collection<int, PublicationFollow>
     */
    public function getPublicationFollows(): Collection
    {
        return $this->publicationFollows;
    }

    public function addPublicationFollow(PublicationFollow $publicationFollow): self
    {
        if (!$this->publicationFollows->contains($publicationFollow)) {
            $this->publicationFollows->add($publicationFollow);
            $publicationFollow->setUser($this);
        }

        return $this;
    }

    public function removePublicationFollow(PublicationFollow $publicationFollow): self
    {
        if ($this->publicationFollows->removeElement($publicationFollow)) {
            // set the owning side to null (unless already changed)
            if ($publicationFollow->getUser() === $this) {
                $publicationFollow->setUser(null);
            }
        }

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customer_name;
    }

    public function setCustomerName(?string $customer_name): self
    {
        $this->customer_name = $customer_name;

        return $this;
    }

    public function getProviderName(): ?string
    {
        return $this->provider_name;
    }

    public function setProviderName(?string $provider_name): self
    {
        $this->provider_name = $provider_name;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }
    public function __toString()
    {
        return $this->username;
    }

    /**
     * @return Collection<int, PublicationRead>
     */
    public function getPublicationReads(): Collection
    {
        return $this->publicationReads;
    }

    public function addPublicationRead(PublicationRead $publicationRead): self
    {
        if (!$this->publicationReads->contains($publicationRead)) {
            $this->publicationReads->add($publicationRead);
            $publicationRead->setUser($this);
        }

        return $this;
    }

    public function removePublicationRead(PublicationRead $publicationRead): self
    {
        if ($this->publicationReads->removeElement($publicationRead)) {
            // set the owning side to null (unless already changed)
            if ($publicationRead->getUser() === $this) {
                $publicationRead->setUser(null);
            }
        }

        return $this;
    }
    public function getLastReadChapterByPublication(int $publicationId): ?PublicationChapter
    {
        $lastPublicationRead = null;

        foreach ($this->publicationReads as $read) {
            if ($read->getChapter()->getPublication()->getId() === $publicationId) {
                if ($lastPublicationRead === null || $read->getReadAt() > $lastPublicationRead->getReadAt()) {
                    $lastPublicationRead = $read;
                }
            }
        }
        return $lastPublicationRead ? $lastPublicationRead->getChapter() : null;
    }
    public function hasReadChapter(int $chapterId): bool
    {
        foreach ($this->publicationReads as $read) {
            if ($read->getChapter()->getId() === $chapterId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Collection<int, ForumTopic>
     */
    public function getForumTopics(): Collection
    {
        return $this->forumTopics;
    }

    public function addForumTopic(ForumTopic $forumTopic): self
    {
        if (!$this->forumTopics->contains($forumTopic)) {
            $this->forumTopics->add($forumTopic);
            $forumTopic->setUser($this);
        }

        return $this;
    }

    public function removeForumTopic(ForumTopic $forumTopic): self
    {
        if ($this->forumTopics->removeElement($forumTopic)) {
            // set the owning side to null (unless already changed)
            if ($forumTopic->getUser() === $this) {
                $forumTopic->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ForumMessage>
     */
    public function getForumMessages(): Collection
    {
        return $this->forumMessages;
    }

    public function addForumMessage(ForumMessage $forumMessage): self
    {
        if (!$this->forumMessages->contains($forumMessage)) {
            $this->forumMessages->add($forumMessage);
            $forumMessage->setUser($this);
        }

        return $this;
    }

    public function removeForumMessage(ForumMessage $forumMessage): self
    {
        if ($this->forumMessages->removeElement($forumMessage)) {
            // set the owning side to null (unless already changed)
            if ($forumMessage->getUser() === $this) {
                $forumMessage->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ForumTopicRead>
     */
    public function getForumTopicReads(): Collection
    {
        return $this->forumTopicReads;
    }

    public function addForumTopicRead(ForumTopicRead $forumTopicRead): self
    {
        if (!$this->forumTopicReads->contains($forumTopicRead)) {
            $this->forumTopicReads->add($forumTopicRead);
            $forumTopicRead->setUser($this);
        }

        return $this;
    }

    public function removeForumTopicRead(ForumTopicRead $forumTopicRead): self
    {
        if ($this->forumTopicReads->removeElement($forumTopicRead)) {
            // set the owning side to null (unless already changed)
            if ($forumTopicRead->getUser() === $this) {
                $forumTopicRead->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ForumTopicView>
     */
    public function getForumTopicViews(): Collection
    {
        return $this->forumTopicViews;
    }

    public function addForumTopicView(ForumTopicView $forumTopicView): self
    {
        if (!$this->forumTopicViews->contains($forumTopicView)) {
            $this->forumTopicViews->add($forumTopicView);
            $forumTopicView->setUser($this);
        }

        return $this;
    }

    public function removeForumTopicView(ForumTopicView $forumTopicView): self
    {
        if ($this->forumTopicViews->removeElement($forumTopicView)) {
            // set the owning side to null (unless already changed)
            if ($forumTopicView->getUser() === $this) {
                $forumTopicView->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationAnnotation>
     */
    public function getPublicationAnnotations(): Collection
    {
        return $this->publicationAnnotations;
    }

    public function addPublicationAnnotation(PublicationAnnotation $publicationAnnotation): self
    {
        if (!$this->publicationAnnotations->contains($publicationAnnotation)) {
            $this->publicationAnnotations->add($publicationAnnotation);
            $publicationAnnotation->setUser($this);
        }

        return $this;
    }

    public function removePublicationAnnotation(PublicationAnnotation $publicationAnnotation): self
    {
        if ($this->publicationAnnotations->removeElement($publicationAnnotation)) {
            // set the owning side to null (unless already changed)
            if ($publicationAnnotation->getUser() === $this) {
                $publicationAnnotation->setUser(null);
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
            $inbox->setUser($this);
        }

        return $this;
    }

    public function removeInbox(Inbox $inbox): self
    {
        if ($this->inboxes->removeElement($inbox)) {
            // set the owning side to null (unless already changed)
            if ($inbox->getUser() === $this) {
                $inbox->setUser(null);
            }
        }

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
            $inboxGroupMember->setUser($this);
        }

        return $this;
    }

    public function removeInboxGroupMember(InboxGroupMember $inboxGroupMember): self
    {
        if ($this->inboxGroupMembers->removeElement($inboxGroupMember)) {
            // set the owning side to null (unless already changed)
            if ($inboxGroupMember->getUser() === $this) {
                $inboxGroupMember->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ForumMessageLike>
     */
    public function getForumMessageLikes(): Collection
    {
        return $this->forumMessageLikes;
    }

    public function addForumMessageLike(ForumMessageLike $forumMessageLike): self
    {
        if (!$this->forumMessageLikes->contains($forumMessageLike)) {
            $this->forumMessageLikes->add($forumMessageLike);
            $forumMessageLike->setUser($this);
        }

        return $this;
    }

    public function removeForumMessageLike(ForumMessageLike $forumMessageLike): self
    {
        if ($this->forumMessageLikes->removeElement($forumMessageLike)) {
            // set the owning side to null (unless already changed)
            if ($forumMessageLike->getUser() === $this) {
                $forumMessageLike->setUser(null);
            }
        }

        return $this;
    }
    public function getTimestamp(): int
    {
        return $this->join_date->getTimestamp();
    }

    /**
     * @return Collection<int, UserFollow>
     */
    public function getUserFollows(): Collection
    {
        return $this->userFollows;
    }

    public function addUserFollow(UserFollow $userFollow): self
    {
        if (!$this->userFollows->contains($userFollow)) {
            $this->userFollows->add($userFollow);
            $userFollow->setFromUser($this);
        }

        return $this;
    }

    public function removeUserFollow(UserFollow $userFollow): self
    {
        if ($this->userFollows->removeElement($userFollow)) {
            // set the owning side to null (unless already changed)
            if ($userFollow->getFromUser() === $this) {
                $userFollow->setFromUser(null);
            }
        }

        return $this;
    }

    public function isFollowing(User $toUser): bool
    {
        foreach ($this->userFollows as $userFollow) {
            if ($userFollow->getToUser() === $toUser) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, PublicationAccess>
     */
    public function getPublicationAccesses(): Collection
    {
        return $this->publicationAccesses;
    }

    public function addPublicationAccess(PublicationAccess $publicationAccess): self
    {
        if (!$this->publicationAccesses->contains($publicationAccess)) {
            $this->publicationAccesses->add($publicationAccess);
            $publicationAccess->setUser($this);
        }

        return $this;
    }

    public function removePublicationAccess(PublicationAccess $publicationAccess): self
    {
        if ($this->publicationAccesses->removeElement($publicationAccess)) {
            // set the owning side to null (unless already changed)
            if ($publicationAccess->getUser() === $this) {
                $publicationAccess->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Challenge>
     */
    public function getChallenges(): Collection
    {
        return $this->challenges;
    }

    public function addChallenge(Challenge $challenge): self
    {
        if (!$this->challenges->contains($challenge)) {
            $this->challenges->add($challenge);
            $challenge->setUser($this);
        }

        return $this;
    }

    public function removeChallenge(Challenge $challenge): self
    {
        if ($this->challenges->removeElement($challenge)) {
            // set the owning side to null (unless already changed)
            if ($challenge->getUser() === $this) {
                $challenge->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationSupport>
     */
    public function getPublicationSupports(): Collection
    {
        return $this->publicationSupports;
    }

    public function addPublicationSupport(PublicationSupport $publicationSupport): self
    {
        if (!$this->publicationSupports->contains($publicationSupport)) {
            $this->publicationSupports->add($publicationSupport);
            $publicationSupport->setUser($this);
        }

        return $this;
    }

    public function removePublicationSupport(PublicationSupport $publicationSupport): self
    {
        if ($this->publicationSupports->removeElement($publicationSupport)) {
            // set the owning side to null (unless already changed)
            if ($publicationSupport->getUser() === $this) {
                $publicationSupport->setUser(null);
            }
        }

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
            $challengeMessage->setUser($this);
        }

        return $this;
    }

    public function removeChallengeMessage(ChallengeMessage $challengeMessage): self
    {
        if ($this->challengeMessages->removeElement($challengeMessage)) {
            // set the owning side to null (unless already changed)
            if ($challengeMessage->getUser() === $this) {
                $challengeMessage->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChallengeMessageLike>
     */
    public function getChallengeMessageLikes(): Collection
    {
        return $this->challengeMessageLikes;
    }

    public function addChallengeMessageLike(ChallengeMessageLike $challengeMessageLike): self
    {
        if (!$this->challengeMessageLikes->contains($challengeMessageLike)) {
            $this->challengeMessageLikes->add($challengeMessageLike);
            $challengeMessageLike->setUser($this);
        }

        return $this;
    }

    public function removeChallengeMessageLike(ChallengeMessageLike $challengeMessageLike): self
    {
        if ($this->challengeMessageLikes->removeElement($challengeMessageLike)) {
            // set the owning side to null (unless already changed)
            if ($challengeMessageLike->getUser() === $this) {
                $challengeMessageLike->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationAnnotationReply>
     */
    public function getPublicationAnnotationReplies(): Collection
    {
        return $this->publicationAnnotationReplies;
    }

    public function addPublicationAnnotationReply(PublicationAnnotationReply $publicationAnnotationReply): self
    {
        if (!$this->publicationAnnotationReplies->contains($publicationAnnotationReply)) {
            $this->publicationAnnotationReplies->add($publicationAnnotationReply);
            $publicationAnnotationReply->setUser($this);
        }

        return $this;
    }

    public function removePublicationAnnotationReply(PublicationAnnotationReply $publicationAnnotationReply): self
    {
        if ($this->publicationAnnotationReplies->removeElement($publicationAnnotationReply)) {
            // set the owning side to null (unless already changed)
            if ($publicationAnnotationReply->getUser() === $this) {
                $publicationAnnotationReply->setUser(null);
            }
        }

        return $this;
    }
}
