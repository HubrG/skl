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
            ->where("p.status = 2");
        $publications = $qb->getQuery()->getResult();
        return $this->render('home/home.html.twig', [
            'controller_name' => "d",
            "publications" => $publications,
            "canonicalUrl" => $this->generateUrl('app_home', array(), true)
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
}
