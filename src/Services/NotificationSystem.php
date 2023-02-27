<?php

namespace App\Services;

use App\Entity\Notification;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class NotificationSystem extends AbstractController
{

    private $notificationRepo;

    private $mailer;
    private $em;

    public function __construct(NotificationRepository $notificationRepo, EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->notificationRepo = $notificationRepo;
        $this->em = $em;
        $this->mailer = $mailer;
    }
    // on annote les paramètrs de cette fonction
    /**
     * @param $type int 
     * Type de notification
     * 
     * 1 : Nouveau commentaire sur l'une de vos publications
     * 
     * 2 : Nouveau commentaire sur l'un de vos chapitres
     * 
     * 3 : Nouveau like sur l'un de vos commentaires
     * 
     * 4 : Nouveau bookmark sur un chapitre
     * 
     * 5 : Noueau téléchargement d'une publication
     * 
     * 6 : Nouveau like sur un chapitre
     * @param $user type
     * @param $message string
     * @param $fromUser type
     * @param $idLink type
     * Cette fonction permet d'ajouter une notification
     * @return void
     */
    public function addNotification($type, $user, $fromUser, $idLink)
    {
        // if ($user == $fromUser) {
        //     return;
        // }
        // $email = (new Email())
        //     ->from('youremail@example.com')
        //     ->to('recipient@example.com');
        // //
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setFromUser($fromUser);
        $notification->setType($type);
        $notification->setCreatedAt(new \DateTimeImmutable());
        $notification->setReadAt(null);
        if ($type === 1 or $type === 2) {
            $notification->setComment($idLink);
            // $email->text('Vous avez reçu un nouveau commentaire sur l\'une de vos publications')->subject('Nouveau commentaire');
        }
        if ($type === 3) {
            $notification->setLikeComment($idLink);
            // $email->text('Vous avez reçu un nouveau like sur un de vos commentaires')->subject('Nouveau like');
        }
        if ($type === 4) {
            $notification->setChapterBookmark($idLink);
            // $email->text('Vous avez reçu un nouveau bookmark sur un de vos chapitres')->subject('Nouveau bookmark');
        }
        if ($type === 5) {
            $notification->setDownload($idLink);
            // $email->text('Vous avez reçu un nouveau téléchargement sur l\'une de vos publications')->subject('Nouveau téléchargement');
        }
        if ($type === 6) {
            $notification->setChapterLike($idLink);
            // $email->text('Vous avez reçu un nouveau like sur un de vos chapitres')->subject('Nouveau like');
        }
        $this->em->persist($notification);
        $this->em->flush();
        //
        // $this->mailer->send($email);
    }
    public function getNotifications()
    {
        $notifications = $this->notificationRepo->findBy(['user' => $this->getUser()]);
        return $notifications;
    }
}
