<?php

namespace App\Components;

use App\Repository\UserRepository;
use App\Repository\ChallengeRepository;
use App\Repository\ForumTopicRepository;
use App\Repository\UserFollowRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsLiveComponent('feed_content_component')]
class FeedContentComponent extends AbstractController
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
        private PublicationChapterLikeRepository $pchlRepo,
        private EntityManagerInterface $em,
        private UserFollowRepository $ufRepo,
        private ChallengeRepository $cRepo
    ) {
    }
    public function getComments(): array
    {
        return $this->pcomRepo->findBy([], ["published_at" => "DESC"], 10);
    }
    // public function getLikes(): array
    // {
    //     // On recherche les likes des commentaire des publications
    //     return $this->pclRepo->findBy([], ["createdAt" => "DESC"], 10);
    // }
    public function getForumMessages(): array
    {
        return $this->fmsgRepo->findBy(["replyTo" => null], ["published_at" => "DESC"], 10);
    }
    public function getChallenges(): array
    {
        return $this->cRepo->findBy([], ["createdAt" => "DESC"], 10);
    }
    public function getForumTopics(): array
    {
        return $this->ftRepo->findBy([], ["createdAt" => "DESC"], 10);
    }
    public function getPublications(): array
    {
        $qb = $this->pubRepo->createQueryBuilder('p');

        $qb->innerJoin('p.publicationChapters', 'pc')
            ->where('p.status = 2')
            ->andWhere('pc.status = 2')
            ->andWhere("p.hideSearch = FALSE")
            ->orderBy('p.created', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
        // return $this->pubRepo->findBy(["status" => 2, "hideSearch" => 0], ["created" => "DESC"], 10);
    }

    public function getPublicationChapters(): array
    {
        $qb = $this->pchRepo->createQueryBuilder('pc')
            ->join('pc.publication', 'p')
            ->where('p.status = 2')
            ->andWhere("p.hideSearch = FALSE")
            ->andWhere('pc.status = 2')
            ->orderBy('pc.published', 'DESC')
            ->setMaxResults(10);

        $chapters = $qb->getQuery()->getResult();

        return $chapters;
    }
    public function getPublicationBookmarks(): array
    {
        return $this->pbmRepo->findBy([], ["createdAt" => "DESC"], 10);
    }
    public function getPublicationAnnotations(): array
    {
        return $this->paRepo->findBy(["mode" => 1], ["createdAt" => "DESC"], 10);
    }
    public function getPublicationReads(): array
    {
        $qb = $this->pchvRepo->createQueryBuilder('p');

        $qb->select('p')
            ->innerJoin('p.chapter', 'pc')
            ->innerJoin('pc.publication', 'pub')
            ->where($qb->expr()->isNotNull('p.user'))
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
        return $this->pfRepo->findBy([], ["CreatedAt" => "DESC"], 10);
    }
    public function getPublicationPopularities(): array
    {
        return $this->ppRepo->findBy([], ["createdAt" => "DESC"], 10);
    }
    public function getUsers(): array
    {
        return $this->userRepo->findBy([], ["join_date" => "DESC"], 10);
    }
    public function getPublicationDownloads(): array
    {
        return $this->pdRepo->findBy([], ["dlAt" => "DESC"], 10);
    }
    public function getPublicationChapterLikes(): array
    {
        return $this->pchlRepo->findBy([], ["CreatedAt" => "DESC"], 10);
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
    // ! 
    // ! 
    // ! 
    // !  FOLLOWED USERS
    // ! 
    // ! 
    // ! 
    // ! 
    public function hadFollowedUsers(): bool
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return false;
        }
        $followedUsers = $this->ufRepo->findBy(['fromUser' => $currentUser]);
        if (count($followedUsers) > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function getAllEntitiesForFollowedUsers(): array
    {

        // Récupérer l'utilisateur courant
        $currentUser = $this->getUser();

        // Récupérer tous les utilisateurs suivis par l'utilisateur courant
        $followedUsers = $this->ufRepo->findBy(['fromUser' => $currentUser]);

        // Extraire les ids des utilisateurs suivis
        $followedUserIds = array_map(fn ($e) => $e->getToUser()->getId(), $followedUsers);

        // Ensuite, dans chaque méthode get pour chaque type d'entité, ajoutez une condition pour filtrer seulement les entités qui sont associées à des utilisateurs suivis
        // Par exemple, pour les commentaires :
        // $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(['user' => $followedUserIds]);

        // Faites la même chose pour les autres types d'entités
        // Ensuite, continuez le reste de votre méthode comme avant
        // Merge all entities into a single array with a unique key
        $entities = array_merge(
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationComment:' . $e->getId()], $this->getCommentsFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'ForumMessage:' . $e->getId()], $this->getForumMessagesFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'ForumTopic:' . $e->getId()], $this->getForumTopicsFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'Publication:' . $e->getId()], $this->getPublicationsFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationChapterLike:' . $e->getId()], $this->getPublicationChapterLikesFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationChapter:' . $e->getId()], $this->getPublicationChaptersFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationBookmark:' . $e->getId()], $this->getPublicationBookmarksFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationAnnotation:' . $e->getId()], $this->getPublicationAnnotationsFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationFollow:' . $e->getId()], $this->getPublicationFollowsFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationDownload:' . $e->getId()], $this->getPublicationDownloadsFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'PublicationRead:' . $e->getId()], $this->getPublicationReadsFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'Challenge:' . $e->getId()], $this->getChallengesFu($followedUserIds)),
            array_map(fn ($e) => ['entity' => $e, 'key' => 'User:' . $e->getId()], $this->getUsersFu($followedUserIds))
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
    public function getCommentsFu($followedUserIds): array
    {
        $qb = $this->pcomRepo->createQueryBuilder('c');
        $qb->innerJoin('c.publication', 'p')
            ->where($qb->expr()->in('c.User', $followedUserIds))
            ->andWhere("p.hideSearch = FALSE")
            ->orderBy('c.published_at', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function getChallengesFu($followedUserIds): array
    {
        $qb = $this->cRepo->createQueryBuilder('c');
        $qb->where($qb->expr()->in('c.user', $followedUserIds))
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function getForumMessagesFu($followedUserIds): array
    {
        $qb = $this->fmsgRepo->createQueryBuilder('f');
        $qb
            ->where('f.replyTo is null')
            ->andWhere($qb->expr()->in('f.user', $followedUserIds))
            ->orderBy('f.published_at', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function getPublicationReadsFu($followedUserIds): array
    {
        $qb = $this->pchvRepo->createQueryBuilder('p');

        $qb->select('p')
            ->innerJoin('p.chapter', 'pc')
            ->innerJoin('pc.publication', 'pub')
            ->where($qb->expr()->isNotNull('p.user'))
            ->andWhere($qb->expr()->in('p.user', $followedUserIds))
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
    public function getPublicationChaptersFu($followedUserIds): array
    {
        $qb = $this->pchRepo->createQueryBuilder('pc');
        $qb
            ->join('pc.publication', 'p')
            ->where('p.status = 2')
            ->andWhere("p.hideSearch = FALSE")
            ->andWhere('pc.status = 2')
            ->andWhere($qb->expr()->in('p.user', $followedUserIds))
            ->orderBy('pc.published', 'DESC')
            ->setMaxResults(10);

        $chapters = $qb->getQuery()->getResult();

        return $chapters;
    }

    public function getForumTopicsFu($followedUserIds): array
    {
        $qb = $this->ftRepo->createQueryBuilder('t');

        $qb->where($qb->expr()->in('t.user', $followedUserIds))
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function getPublicationsFu($followedUserIds): array
    {
        $qb = $this->pubRepo->createQueryBuilder('p');

        $qb->innerJoin('p.publicationChapters', 'pc')
            ->where('p.status = 2')
            ->andWhere("p.hideSearch = FALSE")
            ->andWhere('pc.status = 2')
            ->andWhere($qb->expr()->in('p.user', $followedUserIds))
            ->orderBy('p.created', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    // On laisse cette fonction tel quel puisque vous utilisez déjà un QueryBuilder

    public function getPublicationBookmarksFu($followedUserIds): array
    {
        $qb = $this->pbmRepo->createQueryBuilder('b');

        $qb->where($qb->expr()->in('b.user', $followedUserIds))
            ->orderBy('b.createdAt', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function getPublicationAnnotationsFu($followedUserIds): array
    {
        $qb = $this->paRepo->createQueryBuilder('a');

        $qb->innerJoin('a.chapter', 'pc')
            ->innerJoin('pc.publication', 'p')
            ->where('a.mode = 1')
            ->andWhere($qb->expr()->in('a.user', $followedUserIds))
            ->andWhere("p.hideSearch = FALSE")
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }


    public function getPublicationFollowsFu($followedUserIds): array
    {
        $qb = $this->pfRepo->createQueryBuilder('f');
        $qb->where($qb->expr()->in('f.user', $followedUserIds))
            ->orderBy('f.CreatedAt', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function getUsersFu($followedUserIds): array
    {
        $qb = $this->userRepo->createQueryBuilder('u');


        $qb->where($qb->expr()->in('u.id', $followedUserIds))
            ->orderBy('u.join_date', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function getPublicationDownloadsFu($followedUserIds): array
    {
        $qb = $this->pdRepo->createQueryBuilder('d');


        $qb->where($qb->expr()->in('d.user', $followedUserIds))
            ->orderBy('d.dlAt', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function getPublicationChapterLikesFu($followedUserIds): array
    {
        $qb = $this->pchlRepo->createQueryBuilder('l');


        $qb->innerJoin('l.chapter', 'pc')
            ->innerJoin('pc.publication', 'p')
            ->where($qb->expr()->in('l.user', $followedUserIds))
            ->andWhere("p.hideSearch = FALSE")
            ->orderBy('l.CreatedAt', 'DESC')
            ->setMaxResults(10);


        return $qb->getQuery()->getResult();
    }
}
