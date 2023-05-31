<?php

namespace App\Components;

use App\Repository\UserRepository;
use App\Repository\ForumTopicRepository;
use App\Repository\PublicationRepository;
use App\Repository\ForumMessageRepository;
use App\Repository\PublicationReadRepository;
use App\Repository\ForumMessageLikeRepository;
use App\Repository\PublicationFollowRepository;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationCommentRepository;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use App\Repository\PublicationBookmarkRepository;
use App\Repository\PublicationDownloadRepository;
use App\Repository\PublicationAnnotationRepository;
use App\Repository\PublicationPopularityRepository;
use App\Repository\PublicationChapterLikeRepository;
use App\Repository\PublicationChapterViewRepository;
use App\Repository\PublicationCommentLikeRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('feed_content_component')]
class FeedContentComponent
{
    use DefaultActionTrait;

    public function __construct(
        private PublicationCommentRepository $pcomRepo,
        private PublicationCommentLikeRepository $pclRepo,
        private ForumMessageRepository $fmsgRepo,
        private ForumMessageLikeRepository $fmsglRepo,
        private ForumTopicRepository $ftRepo,
        private PublicationRepository $pubRepo,
        private PublicationChapterRepository $pchRepo,
        private PublicationBookmarkRepository $pbmRepo,
        private PublicationAnnotationRepository $paRepo,
        private PublicationChapterViewRepository $pchvRepo,
        private PublicationFollowRepository $pfRepo,
        private PublicationPopularityRepository $ppRepo,
        private PublicationReadRepository $prRepo,
        private UserRepository $userRepo,
        private PublicationDownloadRepository $pdRepo,
    ) {
    }
    public function getComments(): array
    {
        return $this->pcomRepo->findBy([], ["published_at" => "DESC"], 5);
    }
    public function getLikes(): array
    {
        // On recherche les likes des commentaire des publications
        $likeComment = $this->pclRepo->findBy([], ["createdAt" => "DESC"], 5);
        // On recherche les likes des messages du forum
        $likeMessage = $this->fmsglRepo->findBy([], ["createdAt" => "DESC"], 5);
        // On fusionne les deux tableaux
        $likes = array_merge($likeComment, $likeMessage);
        // On trie le tableau par date de création
        usort($likes, function ($a, $b) {
            return $a->getCreatedAt() <=> $b->getCreatedAt();
        });
        // On retourne les 5 derniers likes
        return array_slice($likes, -5);
    }
    public function getForumMessages(): array
    {
        return $this->fmsgRepo->findBy([], ["published_at" => "DESC"], 5);
    }
    public function getForumTopics(): array
    {
        return $this->ftRepo->findBy([], ["createdAt" => "DESC"], 5);
    }
    public function getPublications(): array
    {
        return $this->pubRepo->findBy(["status" => 2], ["created" => "DESC"], 5);
    }
    public function getPublicationChapters(): array
    {
        return $this->pchRepo->findBy(["status" => 2], ["created" => "DESC"], 5);
    }
    public function getPublicationBookmarks(): array
    {
        return $this->pbmRepo->findBy([], ["createdAt" => "DESC"], 5);
    }
    public function getPublicationAnnotations(): array
    {
        return $this->paRepo->findBy(["mode" => 1], ["createdAt" => "DESC"], 5);
    }
    public function getPublicationChapterViews(): array
    {
        return $this->pchvRepo->findBy([], ["view_date" => "DESC"], 5);
    }
    public function getPublicationFollows(): array
    {
        return $this->pfRepo->findBy([], ["CreatedAt" => "DESC"], 5);
    }
    public function getPublicationPopularities(): array
    {
        return $this->ppRepo->findBy([], ["createdAt" => "DESC"], 5);
    }
    // public function getPublicationReads(): array
    // {
    //     return $this->prRepo->findBy([], ["createdAt" => "DESC"], 5);
    // }
    public function getUsers(): array
    {
        return $this->userRepo->findBy([], ["join_date" => "DESC"], 5);
    }
    public function getPublicationDownloads(): array
    {
        return $this->pdRepo->findBy([], ["dlAt" => "DESC"], 5);
    }
}
