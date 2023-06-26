<?php

namespace App\Components;

use App\Entity\ChallengeMessage;
use App\Repository\UserRepository;
use App\Repository\ChallengeRepository;
use App\Repository\ForumTopicRepository;
use App\Repository\UserFollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\ForumMessageRepository;
use App\Repository\PublicationReadRepository;
use App\Repository\ChallengeMessageRepository;
use App\Repository\ForumMessageLikeRepository;
use App\Repository\PublicationFollowRepository;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationCommentRepository;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use App\Repository\PublicationBookmarkRepository;
use App\Repository\PublicationDownloadRepository;
use App\Repository\PublicationAnnotationRepository;
use App\Repository\PublicationPopularityRepository;
use App\Repository\PublicationChapterLikeRepository;
use App\Repository\PublicationChapterViewRepository;
use App\Repository\PublicationCommentLikeRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsLiveComponent('feed_user_nav_content_component')]
class FeedUserNavContentComponent extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $userId;
    public function __construct(
        private PublicationCommentRepository $pcomRepo,
        private PublicationCommentLikeRepository $pclRepo,
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
        private PublicationChapterLikeRepository $pchlRepo,
        private EntityManagerInterface $em,
        private UserFollowRepository $ufRepo,
        private ForumMessageRepository $fmsgRepo,
        private ForumMessageLikeRepository $fmsglRepo,
        private ForumTopicRepository $ftRepo,
        private ChallengeRepository $cRepo,
        private ChallengeMessageRepository $cmRepo
    ) {
    }
    public function getComments(): array
    {
        return $this->pcomRepo->findBy(["replyTo" => null, "User" => $this->userId], ["published_at" => "DESC"]);
    }
    // public function getLikes(): array
    // {
    //     // On recherche les likes des commentaire des publications
    //     return $this->pclRepo->findBy([], ["createdAt" => "DESC"], 10);
    // }
    public function getForumMessages(): array
    {
        return $this->fmsgRepo->findBy(["replyTo" => null, "user" => $this->userId], ["published_at" => "DESC"]);
    }
    public function getChallengeMessages(): array
    {
        return $this->cmRepo->findBy(["replyTo" => null, "user" => $this->userId], ["publishedAt" => "DESC"]);
    }
    public function getChallenges(): array
    {
        return $this->cRepo->findBy(["user" => $this->userId], ["createdAt" => "DESC"]);
    }
    public function getForumTopics(): array
    {
        return $this->ftRepo->findBy(["user" => $this->userId], ["createdAt" => "DESC"]);
    }
    public function getPublications(): array
    {
        $qb = $this->pubRepo->createQueryBuilder('p');

        $qb->innerJoin('p.publicationChapters', 'pc')
            ->where('p.status = 2')
            ->andWhere('p.user = :user')
            ->andWhere('pc.status = 2')
            ->andWhere("p.hideSearch = FALSE")
            ->orderBy('p.created', 'DESC')
            ->setParameter('user', $this->userId);

        return $qb->getQuery()->getResult();
    }

    public function getPublicationChapters(): array
    {
        $qb = $this->pchRepo->createQueryBuilder('pc')
            ->join('pc.publication', 'p')
            ->where('p.status = 2')
            ->andWhere('p.user = :user')
            ->andWhere("p.hideSearch = FALSE")
            ->andWhere('pc.status = 2')
            ->orderBy('pc.published', 'DESC')
            ->setParameter('user', $this->userId);

        $chapters = $qb->getQuery()->getResult();

        return $chapters;
    }
    public function getPublicationBookmarks(): array
    {
        return $this->pbmRepo->findBy(["user" => $this->userId], ["createdAt" => "DESC"]);
    }
    public function getPublicationAnnotations(): array
    {
        return $this->paRepo->findBy(["user" => $this->userId, "mode" => 1], ["createdAt" => "DESC"]);
    }
    public function getPublicationReads(): array
    {
        $qb = $this->pchvRepo->createQueryBuilder('p');

        $qb->select('p')
            ->innerJoin('p.chapter', 'pc')
            ->innerJoin('pc.publication', 'pub')
            ->where('p.user = ' . $this->userId)
            ->andWhere("pub.hideSearch = FALSE")
            ->orderBy('p.view_date', 'ASC'); // Fetch readings from oldest to newest

        $reads = $qb->getQuery()->getResult();

        $uniqueReads = [];
        $uniqueKeys = [];

        foreach ($reads as $read) {
            $key = $read->getUser()->getId() . ':' . $read->getChapter()->getId();

            // If this user-publication combination has not been encountered yet, keep the reading
            if (!array_key_exists($key, $uniqueKeys)) {
                $uniqueKeys[$key] = $read;
                $uniqueReads[] = $read;
            }
        }

        // Sort the unique readings by view date in descending order for display
        uasort($uniqueReads, function ($a, $b) {
            return $b->getViewDate() <=> $a->getViewDate();
        });

        // Take the 10 most recent readings based on view date
        $uniqueReads = array_slice($uniqueReads, 0, 10);

        return $uniqueReads;
    }
    public function getPublicationFollows(): array
    {
        return $this->pfRepo->findBy(["user" => $this->userId], ["CreatedAt" => "DESC"]);
    }
    public function getPublicationPopularities(): array
    {
        return $this->ppRepo->findBy(["user" => $this->userId], ["createdAt" => "DESC"]);
    }
    public function getUsers(): array
    {
        return $this->userRepo->findBy(["id" => $this->userId], ["join_date" => "DESC"]);
    }
    public function getPublicationDownloads(): array
    {
        return $this->pdRepo->findBy(["user" => $this->userId], ["dlAt" => "DESC"]);
    }
    public function getPublicationChapterLikes(): array
    {
        return $this->pchlRepo->findBy(["user" => $this->userId], ["CreatedAt" => "DESC"]);
    }
    public function getAllEntities(): array
    {
        // Merge all entities into a single array with a unique key
        $entities = array_merge(
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationComment:' . $e->getId()], $this->getComments()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'ForumMessage:' . $e->getId()], $this->getForumMessages()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'ChallengeMessage:' . $e->getId()], $this->getChallengeMessages()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'ForumTopic:' . $e->getId()], $this->getForumTopics()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'Publication:' . $e->getId()], $this->getPublications()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationChapterLike:' . $e->getId()], $this->getPublicationChapterLikes()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationChapter:' . $e->getId()], $this->getPublicationChapters()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationBookmark:' . $e->getId()], $this->getPublicationBookmarks()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationAnnotation:' . $e->getId()], $this->getPublicationAnnotations()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationFollow:' . $e->getId()], $this->getPublicationFollows()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationDownload:' . $e->getId()], $this->getPublicationDownloads()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationRead:' . $e->getId()], $this->getPublicationReads()),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'Challenge:' . $e->getId()], $this->getChallenges()),
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

        // Limit the total number of results to 10


        return $entities;
    }
}
