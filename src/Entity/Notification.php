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
	private ?PublicationDownload $download = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?PublicationChapterLike $chapter_like = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?PublicationChapter $publication_follow = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?Publication $publication_follow_add = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?PublicationBookmark $publication_bookmark = null;

	#[ORM\ManyToOne(inversedBy: 'notificationsReply')]
	private ?PublicationComment $reply_comment = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?PublicationAnnotation $revisionComment = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?ForumMessage $forumMessage = null;

	#[ORM\ManyToOne(inversedBy: 'notificationAssigns')]
	private ?ForumMessage $assignForumMessage = null;

	#[ORM\ManyToOne(inversedBy: 'notificationAssigns')]
	private ?PublicationComment $assignComment = null;

	#[ORM\ManyToOne(inversedBy: 'notificationAssignForumTopic')]
	private ?ForumTopic $assignForumTopic = null;

	#[ORM\ManyToOne(inversedBy: 'notificationReplies')]
	private ?ForumMessage $replyForum = null;

	#[ORM\ManyToOne(inversedBy: 'notificationLikes')]
	private ?ForumMessage $likeForum = null;

	#[ORM\ManyToOne(inversedBy: 'notificationAssignForumMessages')]
	private ?ForumMessage $assignForumReply = null;

	#[ORM\ManyToOne(inversedBy: 'notifications')]
	private ?UserFollow $newFriend = null;

	#[ORM\ManyToOne(inversedBy: 'notificationsFriendNewPub')]
	private ?Publication $friendNewPub = null;




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

	public function getPublicationFollow(): ?PublicationChapter
	{
		return $this->publication_follow;
	}

	public function setPublicationFollow(?PublicationChapter $publication_follow): self
	{
		$this->publication_follow = $publication_follow;

		return $this;
	}

	public function getPublicationFollowAdd(): ?Publication
	{
		return $this->publication_follow_add;
	}

	public function setPublicationFollowAdd(?Publication $publication_follow_add): self
	{
		$this->publication_follow_add = $publication_follow_add;

		return $this;
	}

	public function getPublicationBookmark(): ?PublicationBookmark
	{
		return $this->publication_bookmark;
	}

	public function setPublicationBookmark(?PublicationBookmark $publication_bookmark): self
	{
		$this->publication_bookmark = $publication_bookmark;

		return $this;
	}

	public function getReplyComment(): ?PublicationComment
	{
		return $this->reply_comment;
	}

	public function setReplyComment(?PublicationComment $reply_comment): self
	{
		$this->reply_comment = $reply_comment;

		return $this;
	}

	public function getRevisionComment(): ?PublicationAnnotation
	{
		return $this->revisionComment;
	}

	public function setRevisionComment(?PublicationAnnotation $revisionComment): self
	{
		$this->revisionComment = $revisionComment;

		return $this;
	}

	public function getForumMessage(): ?ForumMessage
	{
		return $this->forumMessage;
	}

	public function setForumMessage(?ForumMessage $forumMessage): self
	{
		$this->forumMessage = $forumMessage;

		return $this;
	}

	public function getAssignForumMessage(): ?ForumMessage
	{
		return $this->assignForumMessage;
	}

	public function setAssignForumMessage(?ForumMessage $assignForumMessage): self
	{
		$this->assignForumMessage = $assignForumMessage;

		return $this;
	}

	public function getAssignComment(): ?PublicationComment
	{
		return $this->assignComment;
	}

	public function setAssignComment(?PublicationComment $assignComment): self
	{
		$this->assignComment = $assignComment;

		return $this;
	}

	public function getAssignForumTopic(): ?ForumTopic
	{
		return $this->assignForumTopic;
	}

	public function setAssignForumTopic(?ForumTopic $assignForumTopic): self
	{
		$this->assignForumTopic = $assignForumTopic;

		return $this;
	}

	public function getReplyForum(): ?ForumMessage
	{
		return $this->replyForum;
	}

	public function setReplyForum(?ForumMessage $replyForum): self
	{
		$this->replyForum = $replyForum;

		return $this;
	}

	public function getLikeForum(): ?ForumMessage
	{
		return $this->likeForum;
	}

	public function setLikeForum(?ForumMessage $likeForum): self
	{
		$this->likeForum = $likeForum;

		return $this;
	}

	public function getAssignForumReply(): ?ForumMessage
	{
		return $this->assignForumReply;
	}

	public function setAssignForumReply(?ForumMessage $assignForumReply): self
	{
		$this->assignForumReply = $assignForumReply;

		return $this;
	}

	public function getNewFriend(): ?UserFollow
	{
		return $this->newFriend;
	}

	public function setNewFriend(?UserFollow $newFriend): self
	{
		$this->newFriend = $newFriend;

		return $this;
	}

	public function getFriendNewPub(): ?Publication
	{
		return $this->friendNewPub;
	}

	public function setFriendNewPub(?Publication $friendNewPub): self
	{
		$this->friendNewPub = $friendNewPub;

		return $this;
	}
}
