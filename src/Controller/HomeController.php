<?php

namespace App\Controller;

use Cloudinary\Cloudinary;
use App\Services\WordCount;
use App\Services\NotificationSystem;
use App\Repository\ForumTopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\ForumMessageRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationCommentRepository;
use App\Repository\PublicationAnnotationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{



    public function __construct(private NotificationSystem $notification, private WordCount $wordCount)
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(PublicationRepository $pRepo, ForumMessageRepository $forumMessageRepository,  ForumTopicRepository $ftRepo, PublicationCommentRepository $pcomRepo): Response
    {
        // *
        // * DERNIÈRES PUBLICATIONS
        $qb = $pRepo->createQueryBuilder("p")
            ->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
            ->where("p.status = 2")
            ->orderBy("p.published_date", "DESC")
            ->groupBy('p.id')
            ->orderBy('MAX(p.published_date)', 'DESC')
            ->setMaxResults(9);
        $publications_last = $qb->getQuery()->getResult();

        // *
        // * PUBLICATIONS LES PLUS POPULAIRES
        $qb = $pRepo->createQueryBuilder("p")
            ->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
            ->where("p.status = 2")
            ->groupBy('p.id')
            ->orderBy('MAX(p.pop)', 'DESC')
            ->setMaxResults(9);
        $publications_pop = $qb->getQuery()->getResult();

        // *
        // * PUBLICATIONS MISES À JOUR
        $qb = $pRepo->createQueryBuilder('p')
            ->leftJoin('p.publicationChapters', 'pc')
            ->where('p.status = 2')
            ->andWhere('pc.status = 2')
            ->groupBy('p.id')
            ->orderBy('MAX(pc.published)', 'DESC')
            ->setMaxResults(9);
        $publications_updated = $qb->getQuery()->getResult();

        // * 
        // * DERNIERS TOPICS
        $qb = $ftRepo->createQueryBuilder("ft")
            ->orderBy("ft.createdAt", "DESC")
            ->setMaxResults(9);
        $topics_last = $qb->getQuery()->getResult();
        // Nombre de nouveaux messages depuis la dernière visite
        if ($this->getUser()) {
            // Récupérer le nombre de messages non lus pour chaque topic
            $unreadMessageCounts = [];
            foreach ($topics_last as $topic) {
                $unreadMessageCounts[$topic->getId()] = $forumMessageRepository->getUnreadMessageCountForUser($this->getUser(), $topic);
            }
        } else {

            $unreadMessageCounts = 0;
        }
        // * 
        // * DERNIERS commentaires
        $qb = $pcomRepo->createQueryBuilder("pcom")
            ->orderBy("pcom.published_at", "DESC")
            ->where("pcom.replyTo is NULL")
            ->setMaxResults(5);
        $comments_last = $qb->getQuery()->getResult();
        // !
        // * RENDER
        return $this->render('home/home.html.twig', [
            'controller_name' => "d",
            "canonicalUrl" => $this->generateUrl('app_home', array(), true),
            'pub_last' => $publications_last,
            'pub_pop' => $publications_pop,
            'is_homepage' => true,
            'pub_updated' => $publications_updated,
            'topics_last' => $topics_last,
            'comments_last' => $comments_last,
            'unreadMessageCounts' => $unreadMessageCounts
        ]);
    }
    #[Route('/clearnotification', name: 'app_notification_clear', methods: ['POST'])]
    public function clearNotification(NotificationRepository $notifRepo, EntityManagerInterface $em): Response
    {

        if (!$this->getUser()) {
            return $this->json([
                'code' => 403,
                'success' => false,
                'message' => 'Vous devez être connecté pour accéder à cette page.'
            ], 403);
        }
        $notif = $notifRepo->findBy(["user" => $this->getUser()]);
        // On set une date de ReadAt
        foreach ($notif as $n) {
            $n->setReadAt(new \DateTimeImmutable());
            $em->persist($n);
            $em->flush();
        }
        return $this->json([
            'code' => 200,
            'success' => true,
            'message' => 'Notification lues.'
        ], 200);
    }
    #[Route('/cgu', name: 'app_cgu')]
    public function cgu(): Response
    {

        // On récupère les publications qui ont le status 2 (publié) et un chapitre publié

        return $this->render('home/cgu.html.twig', [
            'controller_name' => "d",
            "canonicalUrl" => $this->generateUrl('app_cgu', array(), true)
        ]);
    }

    #[Route('/privacy', name: 'app_privacy')]
    public function privacy(): Response
    {
        // On récupère les publications qui ont le status 2 (publié) et un chapitre publié
        return $this->render('home/privacy.html.twig', [
            'controller_name' => "d",
            "canonicalUrl" => $this->generateUrl('app_privacy', array(), true)
        ]);
    }
    #[Route('/test', name: 'app_test')]
    public function test(PublicationAnnotationRepository $paRepo): Response
    {



        return $this->render('home/test.html.twig', [
            'controller_name' => "d",

            "article" => "dd"

        ]);
    }
}
