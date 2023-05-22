<?php

namespace App\Services;

use Twig\Environment;
use App\Entity\Notification;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class NotificationSystem extends AbstractController
{

    private $notificationRepo;

    private $twig;
    private $mailer;
    private $em;
    private $userRepo;

    public function __construct(UserRepository $userRepo, NotificationRepository $notificationRepo, EntityManagerInterface $em, MailerInterface $mailer, Environment $twig)
    {
        $this->notificationRepo = $notificationRepo;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->userRepo = $userRepo;
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
     * 
     * 7 : Nouvelle feuille sur une publication que vous suivez
     * 
     * 8 : Nouvel abonnement à votre publication
     * 
     * 9 : Nouvelle réponse à un commentaire
     * 
     * 10 : Nouvelle révision sur un chapitre de votre publication
     * 
     * 11 : Nouvelle réponse sur l'un de vos sujets de forum
     * @param $user type
     * @param $message string
     * @param $fromUser type
     * @param $idLink type
     * Cette fonction permet d'ajouter une notification
     * @return void
     */
    public function addNotification($type, $user, $fromUser, $idLink)
    {
        if ($user == $fromUser) {
            return;
        }
        $userRepo = $this->userRepo->find($user);
        $email = (new TemplatedEmail())
            ->from(new Address('admin@scrilab.com', 'Scrilab'))
            ->to($user->getEmail())
            ->htmlTemplate('emails/notif_template.html.twig');
        // //
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setFromUser($fromUser);
        $notification->setType($type);
        $notification->setCreatedAt(new \DateTimeImmutable());
        $notification->setReadAt(null);
        // 
        $pathUserFrom = $this->generateUrl('app_user', ['username' => $notification->getFromUser()->getUsername()]);
        //
        if ($type === 1) {
            if (is_null($userRepo->getUserParameters()->isNotif1Web()) or $userRepo->getUserParameters()->isNotif1Web() == 1) {
                $notification->setComment($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setComment($idLink);
            }
            // Envoi d'email
            if (is_null($userRepo->getUserParameters()->isNotif1Mail()) or $userRepo->getUserParameters()->isNotif1Mail() == 1) {
                if ($notification->getComment()->getChapter()) {
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getComment()->getPublication()->getSlug(), "user" => $notification->getComment()->getUser()->getUsername(), "idChap" => $notification->getComment()->getChapter()->getId(), "slug" => $notification->getComment()->getChapter()->getSlug()]);
                    $textChapter = "sur la feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getComment()->getChapter()->getTitle() . "</a>";
                    $textSubject = "Nouveau commentaire sur l'une de vos feuilles.";
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                    $textSubject = "Nouveau commentaire sur l'un de vos récits.";
                }
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getComment()->getPublication()->getSlug(), "id" => $notification->getComment()->getPublication()->getId()]);
                $textPublication = " du récit <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getComment()->getPublication()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                     vient d'écrire un commentaire " . $textChapter . $textPublication . "<br/>",
                        'subject' => "Nouveau commentaire sur l'une de vos publications.",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 2) {
            if (is_null($userRepo->getUserParameters()->isNotif2Web()) or $userRepo->getUserParameters()->isNotif2Web() == 1) {
                $notification->setComment($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setLikeComment($idLink);
            }
            // Envoi d'email
            if (is_null($userRepo->getUserParameters()->isNotif2Mail()) or $userRepo->getUserParameters()->isNotif2Mail() == 1) {
                if ($notification->getComment()->getChapter()) {
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getComment()->getPublication()->getSlug(), "user" => $notification->getComment()->getUser()->getUsername(), "idChap" => $notification->getComment()->getChapter()->getId(), "slug" => $notification->getComment()->getChapter()->getSlug()]);
                    $textChapter = "sur la feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getComment()->getChapter()->getTitle() . "</a>";
                    $textSubject = "Nouveau commentaire sur l'une de vos feuilles.";
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                    $textSubject = "Nouveau commentaire sur l'un de vos récits.";
                }
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getComment()->getPublication()->getSlug(), "id" => $notification->getComment()->getPublication()->getId()]);
                $textPublication = " du récit <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getComment()->getPublication()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient d'écrire un commentaire " . $textChapter . $textPublication . "<br/>",
                        'subject' => "Nouveau commentaire sur l'une de vos publications.",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 3) {
            if (is_null($userRepo->getUserParameters()->isNotif3Web()) or $userRepo->getUserParameters()->isNotif3Web() == 1) {
                $notification->setLikeComment($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setLikeComment($idLink);
            }
            if (is_null($userRepo->getUserParameters()->isNotif3Mail()) or $userRepo->getUserParameters()->isNotif3Mail() == 1) {
                // Envoi d'email
                if ($notification->getLikeComment()->getComment()->getChapter()) {
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getLikeComment()->getComment()->getPublication()->getSlug(), "user" => $notification->getLikeComment()->getUser()->getUsername(), "idChap" => $notification->getLikeComment()->getComment()->getChapter()->getId(), "slug" => $notification->getLikeComment()->getComment()->getChapter()->getSlug()]);
                    $textChapter = "sur la feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getLikeComment()->getComment()->getChapter()->getTitle() . "</a>";
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                }
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getLikeComment()->getComment()->getPublication()->getSlug(), "id" => $notification->getLikeComment()->getComment()->getPublication()->getId()]);
                $textPublication = " du récit <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getLikeComment()->getComment()->getPublication()->getTitle() . "</a>";
                $email->subject("Nouveau « J'aime » sur l'un de vos commentaires.")
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                    vient d'aimer votre commentaire " . $textChapter . $textPublication . "<br/>
                    <blockquote style='font-style: italic;text-align:center;'>« " . $notification->getLikeComment()->getComment()->getContent() . " »</blockquote>",
                        'subject' => "Nouveau « J'aime » sur l'un de vos commentaires.",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 4) {
            if (is_null($userRepo->getUserParameters()->isNotif4Web()) or $userRepo->getUserParameters()->isNotif4Web() == 1) {
                $notification->setPublicationBookmark($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setPublicationBookmark($idLink);
            }
            // Envoi d'email
            if (is_null($userRepo->getUserParameters()->isNotif4Mail()) or $userRepo->getUserParameters()->isNotif4Mail() == 1) {
                if ($notification->getPublicationBookmark()->getChapter()) {
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getPublicationBookmark()->getChapter()->getPublication()->getSlug(), "user" => $notification->getPublicationBookmark()->getUser()->getUsername(), "idChap" => $notification->getPublicationBookmark()->getChapter()->getId(), "slug" => $notification->getPublicationBookmark()->getChapter()->getSlug()]);
                    $textChapter = " la feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getPublicationBookmark()->getChapter()->getTitle() . "</a>";
                    $textSubject = "L'une de vos feuilles a été ajoutée à une collection.";
                    $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getPublicationBookmark()->getChapter()->getPublication()->getSlug(), "id" => $notification->getPublicationBookmark()->getChapter()->getPublication()->getId()]);
                    $textPublication = " le récit <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getPublicationBookmark()->getChapter()->getPublication()->getTitle() . "</a>";
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                    $textSubject = "L'un de vos récits a été ajouté à une collection.";
                    $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getPublicationBookmark()->getPublication()->getSlug(), "id" => $notification->getPublicationBookmark()->getPublication()->getId()]);
                    $textPublication = " le récit <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getPublicationBookmark()->getPublication()->getTitle() . "</a>";
                }
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                    vient d'ajouter " . $textChapter . $textPublication . " à sa collection<br/>",
                        'subject' => $textSubject,
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 5) {
            $notification->setDownload($idLink);
            // $email->text('Vous avez reçu un nouveau téléchargement sur l\'une de vos publications')->subject('Nouveau téléchargement');
        }
        if ($type === 6) {
            if (is_null($userRepo->getUserParameters()->isNotif6Web()) or $userRepo->getUserParameters()->isNotif6Web() == 1) {
                $notification->setChapterLike($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setChapterLike($idLink);
            }
            // Envoi d'email
            if (is_null($userRepo->getUserParameters()->isNotif6Mail()) or $userRepo->getUserParameters()->isNotif6Mail() == 1) {
                $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getChapterLike()->getChapter()->getPublication()->getSlug(), "user" => $notification->getChapterLike()->getChapter()->getPublication()->getUser()->getUsername(), "idChap" => $notification->getChapterLike()->getChapter()->getId(), "slug" => $notification->getChapterLike()->getChapter()->getSlug()]);
                $textChapter = " la feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getChapterLike()->getChapter()->getTitle() . "</a>";
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getChapterLike()->getChapter()->getPublication()->getSlug(), "id" => $notification->getChapterLike()->getChapter()->getPublication()->getId()]);
                $textPublication = " du récit <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getChapterLike()->getChapter()->getPublication()->getTitle() . "</a>";
                $email->subject("Nouveau « J'aime » sur l'une de vos feuilles.")
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                    vient d'aimer " . $textChapter . $textPublication . "<br/>",
                        'subject' => "Nouveau « J'aime » sur l'une de vos feuilles.",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 7) {
            if (is_null($userRepo->getUserParameters()->isNotif7Web()) or $userRepo->getUserParameters()->isNotif7Web() == 1) {
                $notification->setPublicationFollow($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setPublicationFollow($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif7Mail()) or $userRepo->getUserParameters()->isNotif7Mail() == 1) {
                $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getPublicationFollow()->getPublication()->getSlug(), "user" => $notification->getPublicationFollow()->getPublication()->getUser()->getUsername(), "idChap" => $notification->getPublicationFollow()->getId(), "slug" => $notification->getPublicationFollow()->getSlug()]);
                $textChapter = " <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getPublicationFollow()->getTitle() . "</a>";
                $textSubject = "Nouvelle feuille publiée !";
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getPublicationFollow()->getPublication()->getSlug(), "id" => $notification->getPublicationFollow()->getPublication()->getId()]);
                $textPublication = " <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getPublicationFollow()->getPublication()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                     vient d'ajouter une nouvelle feuille à son récit " . $textPublication . " : <br/><br/><big>" . $textChapter . "</big>",
                        'subject' => "Nouvelle feuille publiée !",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 8) {
            if (is_null($userRepo->getUserParameters()->isNotif8Web()) or $userRepo->getUserParameters()->isNotif8Web() == 1) {
                $notification->setPublicationFollowAdd($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setPublicationFollowAdd($idLink);
            }
            // Envoi d'email
            if (is_null($userRepo->getUserParameters()->isNotif8Mail()) or $userRepo->getUserParameters()->isNotif8Mail() == 1) {
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getPublicationFollowAdd()->getSlug(), "id" => $notification->getPublicationFollowAdd()->getId()]);
                $textPublication = " <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getPublicationFollowAdd()->getTitle() . "</a>";
                $email->subject("Nouvel abonnement à l'un de vos récits.")
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                    vient de s'abonner à votre récit " . $textPublication . "<br/>",
                        'subject' => "Nouvel abonnement à l'un de vos récits.",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 9) {
            if (is_null($userRepo->getUserParameters()->isNotif9Web()) or $userRepo->getUserParameters()->isNotif9Web() == 1) {
                $notification->setReplyComment($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setReplyComment($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif9Mail()) or $userRepo->getUserParameters()->isNotif9Mail() == 1) {
                if ($notification->getReplyComment()->getChapter()) {
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getReplyComment()->getPublication()->getSlug(), "user" => $notification->getReplyComment()->getUser()->getUsername(), "idChap" => $notification->getReplyComment()->getChapter()->getId(), "slug" => $notification->getReplyComment()->getChapter()->getSlug()]);
                    $textChapter = "sur la feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getReplyComment()->getChapter()->getTitle() . "</a>";
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                }
                $textSubject = "Nouvelle réponse sous l'un de vos commentaires";
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getReplyComment()->getPublication()->getSlug(), "id" => $notification->getReplyComment()->getPublication()->getId()]);
                $textPublication = ", du récit <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getReplyComment()->getPublication()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                     vient de répondre à votre commentaire " . $textChapter . $textPublication . "<br/>
                     <blockquote style='font-style: italic;text-align:center;'><strong>Votre commentaire :</strong><br>« " . $notification->getReplyComment()->getReplyTo()->getContent() . " »</blockquote>
                     <blockquote style='font-style: italic;text-align:center;'><strong>Réponse à votre commentaire :</strong><br>« " . $notification->getReplyComment()->getContent() . " »</blockquote>",
                        'subject' => "Nouvelle réponse sous l'un de vos commentaires",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 10) {
            if (is_null($userRepo->getUserParameters()->isNotif10Web()) or $userRepo->getUserParameters()->isNotif10Web() == 1) {
                $notification->setRevisionComment($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setRevisionComment($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif10Mail()) or $userRepo->getUserParameters()->isNotif10Mail() == 1) {
                if ($notification->getRevisionComment()->getChapter()) {
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getRevisionComment()->getChapter()->getPublication()->getSlug(), "user" => $notification->getRevisionComment()->getUser()->getUsername(), "idChap" => $notification->getRevisionComment()->getChapter()->getId(), "slug" => $notification->getRevisionComment()->getChapter()->getSlug()]);
                    $textChapter = "de votre feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getRevisionComment()->getChapter()->getTitle() . "</a>";
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                }
                $textSubject = "Nouveau commentaire de révision sur l'un de vos chapitres";
                $pathPublication = $this->generateUrl('app_chapter_revision', ['slug' => $notification->getRevisionComment()->getChapter()->getPublication()->getSlug(), "user" => $notification->getRevisionComment()->getChapter()->getPublication()->getUser()->getId(), "slugPub" => $notification->getRevisionComment()->getChapter()->getPublication()->getSlug(), "idChap" => $notification->getRevisionComment()->getChapter()->getPublication()->getId()]);
                $textPublication = ", du récit <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getRevisionComment()->getChapter()->getPublication()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient de vous suggérer une modification pour améliorer le texte " . $textChapter . $textPublication,
                        'subject' => "Nouveau commentaire de révision sur l'un de vos chapitres",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 11) {
            if (is_null($userRepo->getUserParameters()->isNotif11Web()) or $userRepo->getUserParameters()->isNotif11Web() == 1) {
                $notification->setForumMessage($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setForumMessage($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif11Mail()) or $userRepo->getUserParameters()->isNotif11Mail() == 1) {
                $textSubject = "Nouvelle réponse sous votre sujet de forum";
                $pathPublication = $this->generateUrl('app_forum_topic_read', ['slug' => $notification->getForumMessage()->getTopic()->getCategory()->getSlug(), "id" => $notification->getForumMessage()->getTopic()->getId(), "slugTopic" => $notification->getForumMessage()->getTopic()->getSlug()]);
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient d'envoyer une réponse sous votre sujet de forum <a href='" . $pathPublication . "'>" . $notification->getForumMessage()->getTopic()->getTitle() . "</a>",
                        'subject' => "Nouvelle réponse sous votre sujet de forum",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
    }
    public function getNotifications()
    {
        $notifications = $this->notificationRepo->findBy(['user' => $this->getUser()]);
        return $notifications;
    }
    public function deleteNotification($type, $id)
    {
        if ($type === 7) {
            $notification = $this->notificationRepo->find($id);
        }
        if ($type === 8) {
            $notification = $this->notificationRepo->find($id);
        }
        $this->em->remove($notification);
        $this->em->flush();
    }
}
