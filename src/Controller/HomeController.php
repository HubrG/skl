<?php

namespace App\Controller;

use Symfony\Component\Mime\Email;
use App\Entity\PublicationComment;
use App\Repository\UserRepository;
use App\Form\PublicationCommentType;
use App\Services\NotificationSystem;
use Symfony\Component\Mailer\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationCommentRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    private $notification;

    public function __construct(NotificationSystem $notification)
    {
        $this->notification = $notification;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request, MailerInterface $mailer, PublicationRepository $pRepo, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        return  $this->render('home/home.html.twig', [
            'controller_name' => "d",
            "test" =>  "d",
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
}
