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
        private PublicationChapterLikeRepository $pchlRepo
    ) {
    }
    public function getComments(): array
    {
        return $this->pcomRepo->findBy([], ["published_at" => "DESC"], 5);
    }
    // public function getLikes(): array
    // {
    //     // On recherche les likes des commentaire des publications
    //     return $this->pclRepo->findBy([], ["createdAt" => "DESC"], 5);
    // }
    public function getForumMessages(): array
    {
        return $this->fmsgRepo->findBy(["replyTo" => null], ["published_at" => "DESC"], 5);
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
        $qb = $this->pchRepo->createQueryBuilder('pc')
            ->join('pc.publication', 'p')
            ->where('p.status = 2')
            ->andWhere('pc.status = 2')
            ->orderBy('pc.published', 'DESC')
            ->setMaxResults(5);

        $chapters = $qb->getQuery()->getResult();

        return $chapters;
    }
    public function getPublicationBookmarks(): array
    {
        return $this->pbmRepo->findBy([], ["createdAt" => "DESC"], 5);
    }
    public function getPublicationAnnotations(): array
    {
        return $this->paRepo->findBy(["mode" => 1], ["createdAt" => "DESC"], 5);
    }
    public function getPublicationReads(): array
    {
        $qb = $this->pchvRepo->createQueryBuilder('p');

        $qb->where($qb->expr()->isNotNull('p.user'))
            ->orderBy('p.view_date', 'DESC')
            ->setMaxResults(5);

        return $qb->getQuery()->getResult();
    }
    public function getPublicationFollows(): array
    {
        return $this->pfRepo->findBy([], ["CreatedAt" => "DESC"], 5);
    }
    public function getPublicationPopularities(): array
    {
        return $this->ppRepo->findBy([], ["createdAt" => "DESC"], 5);
    }
    public function getUsers(): array
    {
        return $this->userRepo->findBy([], ["join_date" => "DESC"], 5);
    }
    public function getPublicationDownloads(): array
    {
        return $this->pdRepo->findBy([], ["dlAt" => "DESC"], 5);
    }
    public function getPublicationChapterLikes(): array
    {
        return $this->pchlRepo->findBy([], ["CreatedAt" => "DESC"], 5);
    }
    public function getAllEntities(): array
    {
        // Merge all entities into a single array with a unique key
        $entities = array_merge(
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationComment:' . $e->getId()], $this->getComments()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'ForumMessage:' . $e->getId()], $this->getForumMessages()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'ForumTopic:' . $e->getId()], $this->getForumTopics()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'Publication:' . $e->getId()], $this->getPublications()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationChapterLike:' . $e->getId()], $this->getPublicationChapterLikes()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationChapter:' . $e->getId()], $this->getPublicationChapters()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationBookmark:' . $e->getId()], $this->getPublicationBookmarks()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationAnnotation:' . $e->getId()], $this->getPublicationAnnotations()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationFollow:' . $e->getId()], $this->getPublicationFollows()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationDownload:' . $e->getId()], $this->getPublicationDownloads()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationRead:' . $e->getId()], $this->getPublicationReads()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'User:' . $e->getId()], $this->getUsers())
        );

        // Remove duplicates based on the unique key
        $entities = array_unique($entities, SORT_REGULAR);

        // Extract the entities from the array
        $entities = array_map(fn ($e) => $e['entity'], $entities);

        // Sort all entities by timestamp (descending order)
        usort($entities, function ($a, $b) {
            return $b->getTimestamp() <=> $a->getTimestamp();
        });

        // Limit the total number of results to 5


        return $entities;
    }
}
