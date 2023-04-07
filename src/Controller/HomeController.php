<?php

namespace App\Controller;

use Cloudinary\Cloudinary;
use App\Services\NotificationSystem;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    private $notification;

    public function __construct(NotificationSystem $notification)
    {
        $this->notification = $notification;
    }

    #[Route('/', name: 'app_home')]
    public function index(PublicationRepository $pRepo): Response
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
        return $this->render('home/home.html.twig', [
            'controller_name' => "d",
            "publications" => $publications,
            "canonicalUrl" => $this->generateUrl('app_home', array(), true),
            'pub_last' => $publications_last, // Retourne les dernières publications
            'pub_pop' => $publications_pop, // Retourne les dernières publications
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
    #[Route('/test', name: 'app_test')]
    public function test(NotificationRepository $notifRepo, EntityManagerInterface $em): Response
    {
        $cloudinary = new Cloudinary(
            [
                'cloud' => [
                    'cloud_name' => 'djaro8nwk',
                    'api_key'    => '716759172429212',
                    'api_secret' => 'A35hPbZP0NsjnMKrE9pLR-EHwiU',
                ],
            ]
        );
        $test = $cloudinary->uploadApi()->upload(
            "images/test.pdf",
            ["ocr" => "adv_ocr"]
        );
        $tests = "";
        foreach ($test['info']['ocr']['adv_ocr']['data'] as $item) {
            $tests = $tests . $item["fullTextAnnotation"]['text'] . "<br><br>";
        }
        $tests = str_replace("\n\n", "<br><br>", $tests);
        $tests = str_replace("\n", "<br><br>", $tests);
        return $this->render('home/test.html.twig', [
            'controller_name' => "d",
            "canonicalUrl" => $this->generateUrl('app_home', array(), true),
            "test" => $tests
        ]);
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
