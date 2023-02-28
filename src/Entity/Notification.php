<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\NotificationRepository;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?User $user = null;

	#[ORM\Column]
	private ?\DateTimeImmutable $CreatedAt = null;

	#[ORM\Column(nullable: true)]
	private ?\DateTimeImmutable $ReadAt = null;





	#[ORM\Column(nullable: true)]
	private ?int $type = null;

	#[ORM\ManyToOne(inversedBy: 'UserFrom')]
	private ?User $from_user = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?PublicationComment $comment = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?PublicationCommentLike $like_comment = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?PublicationChapterBookmark $chapter_bookmark = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?PublicationDownload $download = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?PublicationChapterLike $chapter_like = null;

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

	public function getCreatedAt(): ?\DateTimeImmutable
	{
		return $this->CreatedAt;
	}

	public function setCreatedAt(\DateTimeImmutable $CreatedAt): self
	{
		$this->CreatedAt = $CreatedAt;

		return $this;
	}

	public function getReadAt(): ?\DateTimeImmutable
	{
		return $this->ReadAt;
	}

	public function setReadAt(?\DateTimeImmutable $ReadAt): self
	{
		$this->ReadAt = $ReadAt;

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

	public function getFromUser(): ?User
	{
		return $this->from_user;
	}

	public function setFromUser(?User $from_user): self
	{
		$this->from_user = $from_user;

		return $this;
	}

	public function getComment(): ?PublicationComment
	{
		return $this->comment;
	}

	public function setComment(?PublicationComment $comment): self
	{
		$this->comment = $comment;

		return $this;
	}

	public function getLikeComment(): ?PublicationCommentLike
	{
		return $this->like_comment;
	}

	public function setLikeComment(?PublicationCommentLike $like_comment): self
	{
		$this->like_comment = $like_comment;

		return $this;
	}

	public function getChapterBookmark(): ?PublicationChapterBookmark
	{
		return $this->chapter_bookmark;
	}

	public function setChapterBookmark(?PublicationChapterBookmark $chapter_bookmark): self
	{
		$this->chapter_bookmark = $chapter_bookmark;

		return $this;
	}

	public function getDownload(): ?PublicationDownload
	{
		return $this->download;
	}

	public function setDownload(?PublicationDownload $download): self
	{
		$this->download = $download;

		return $this;
	}

	public function getChapterLike(): ?PublicationChapterLike
	{
		return $this->chapter_like;
	}

	public function setChapterLike(?PublicationChapterLike $chapter_like): self
	{
		$this->chapter_like = $chapter_like;

		return $this;
	}
}