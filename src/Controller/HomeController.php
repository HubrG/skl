<?php

namespace App\Controller;

use Cloudinary\Cloudinary;
use App\Services\NotificationSystem;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationCommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    private $notification;

    public function __construct(NotificationSystem $notification)
    {
        $this->notification = $notification;
    }

    #[Route('/', name: 'app_home')]
    public function index(PublicationRepository $pRepo, PublicationChapterRepository $pchRepo, PublicationCommentRepository $pcomRepo): Response
    {

        // On récupère les publications qui ont le status 2 (publié) et un chapitre publié
        $qb = $pRepo->createQueryBuilder("p")
            ->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
            ->where("p.status = 2")
            ->orderBy("p.published_date", "DESC");
        $publications = $qb->getQuery()->getResult();
        $publications_last = $pRepo->findBy(["id" => $publications], ["published_date" => "desc"], 6);
        //
        $qb = $pRepo->createQueryBuilder("p")
            ->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
            ->where("p.status = 2")
            ->orderBy("p.pop", "DESC");
        $publications = $qb->getQuery()->getResult();
        $publications_pop = $pRepo->findBy(["id" => $publications], ["pop" => "desc"], 6);
        // // Derniers chapitres publiés en status 2, limités à 8, et dont la publication est publiée
        // $qb = $pchRepo->createQueryBuilder("pch")
        //     ->innerJoin("pch.publication", "p", "WITH", "p.status = 2")
        //     ->where("pch.status = 2")
        //     ->orderBy("pch.published", "DESC");
        // $publications_chapters = $qb->getQuery()->getResult();
        // $publications_chapters = $pchRepo->findBy(["id" => $publications_chapters], ["published" => "desc"], 8);
        // Derniers commentaires publiés, limités à 8, et dont le chapitre ou la publication est publiée
        $qb = $qb = $pcomRepo->createQueryBuilder("pcom")
            ->leftJoin("pcom.chapter", "pch", "WITH", "pch.status = 2")
            ->innerJoin("pcom.publication", "p", "WITH", "p.status = 2")
            ->where("pcom.replyTo IS NULL");
        $publications_comments = $qb->getQuery()->getResult();
        $publications_comments = $pcomRepo->findBy(["id" => $publications_comments], ["published_at" => "desc"], 8);
        //
        // Derniers chapitres publiés en status 2, limités à 8, et dont la publication est publiée (last version)
        $qb = $pRepo->createQueryBuilder('p')
            ->leftJoin('p.publicationChapters', 'pc')
            ->where('p.status = 2')
            ->andWhere('pc.status = 2')
            ->orderBy('pc.published', 'DESC');
        $publications_updated = $qb->getQuery()->getResult();
        // dd($publications_updated);
        //
        return $this->render('home/home.html.twig', [
            'controller_name' => "d",
            "publications" => $publications,
            "canonicalUrl" => $this->generateUrl('app_home', array(), true),
            'pub_last' => $publications_last, // Retourne les dernières publications
            'pub_pop' => $publications_pop, // Retourne les dernières publications
            'is_homepage' => true,
            // 'chaps_last' => $publications_chapters,
            'coms_last' => $publications_comments,
            'pub_updated' => $publications_updated
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
}
