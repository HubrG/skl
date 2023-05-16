<?php

namespace App\Controller;

use App\Repository\InboxRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LiveNotificationController extends AbstractController
{
    #[Route('/live/notification', name: 'app_live_notification', methods: ['POST'])]
    public function index(InboxRepository $inboxRepo): Response
    {
        // ! On recherche le nombre de messages non lus
        $nbUnreadMessages = count($inboxRepo->findBy(["UserTo" => $this->getUser(), "ReadAt" => null]));
        return $this->json([
            'newMessage' => $nbUnreadMessages
        ]);
    }
}
