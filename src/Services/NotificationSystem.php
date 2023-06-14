<?php

namespace App\Services;

use Twig\Environment;
use App\Services\Assign;
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
     * 
     * 12 : Nouvelle mention sur une réponse d'un sujet du forum ———— STOPED, remplacé par le °17
     * 
     * 13 : Nouvelle mention sur un sujet du forum
     * 
     * 14 : Nouvelle mention sur un commentaire    
     *  
     * 15 : Nouvelle réponse sur l'une de vos réponses forum
     *  
     * 16 : Nouveau j'aime sur votre réponse de forum
     *  
     * 17 : Nouvelle mention sur une réponse à un sujet de forum
     *  
     * 18 : Nouvel abonné
     *  
     * 19 : Nouveau récit publié par l'un de vos abonnements
     *  
     * 20 : Nouveau commentaire sous un challenge
     *  
     * 21 : Proposition de réponse à un challenge
     *  
     * 22 : Mention dans un énoncé de challenge
     *  
     * 23 : Mention dans une réponse à un challenge
     *  
     * 24 : Nouveau « J'aime » dans une réponse à un challenge
     *   
     * 25 : Nouvelle réponse sur l'une de vos réponses challenge
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
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getComment()->getPublication()->getSlug(), "id" => $notification->getComment()->getPublication()->getId(), "idCom" => $notification->getComment()->getId()]);
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
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getComment()->getPublication()->getSlug(), "user" => $notification->getComment()->getUser()->getUsername(), "idChap" => $notification->getComment()->getChapter()->getId(), "slug" => $notification->getComment()->getChapter()->getSlug(), "idCom" => $notification->getComment()->getId()]);
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
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getLikeComment()->getComment()->getPublication()->getSlug(), "user" => $notification->getLikeComment()->getUser()->getUsername(), "idChap" => $notification->getLikeComment()->getComment()->getChapter()->getId(), "slug" => $notification->getLikeComment()->getComment()->getChapter()->getSlug(), "idCom" => $notification->getLikeComment()->getComment()->getId()]);
                    $textChapter = "sur la feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getLikeComment()->getComment()->getChapter()->getTitle() . "</a>";
                    $showCom = false;
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                    $showCom = true;
                }
                if ($showCom) {
                    $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getLikeComment()->getComment()->getPublication()->getSlug(), "id" => $notification->getLikeComment()->getComment()->getPublication()->getId(), "idCom" => $notification->getLikeComment()->getComment()->getId()]);
                } else {
                    $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getLikeComment()->getComment()->getPublication()->getSlug(), "id" => $notification->getLikeComment()->getComment()->getPublication()->getId()]);
                }
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
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getReplyComment()->getPublication()->getSlug(), "user" => $notification->getReplyComment()->getUser()->getUsername(), "idChap" => $notification->getReplyComment()->getChapter()->getId(), "slug" => $notification->getReplyComment()->getChapter()->getSlug(), "idCom" => $notification->getReplyComment()->getId()]);
                    $textChapter = "sur la feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getReplyComment()->getChapter()->getTitle() . "</a>";
                    $showCom = false;
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                    $showCom = true;
                }
                $textSubject = "Nouvelle réponse sous l'un de vos commentaires";
                if ($showCom) {
                    $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getReplyComment()->getPublication()->getSlug(), "id" => $notification->getReplyComment()->getPublication()->getId(), "idCom" => $notification->getReplyComment()->getId()]);
                } else {
                    $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getReplyComment()->getPublication()->getSlug(), "id" => $notification->getReplyComment()->getPublication()->getId()]);
                }
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
                    $pathChapter = $this->generateUrl('app_chapter_revision', ['slug' => $notification->getRevisionComment()->getChapter()->getSlug(), "user" => $notification->getRevisionComment()->getChapter()->getPublication()->getUser()->getId(), "slugPub" => $notification->getRevisionComment()->getChapter()->getPublication()->getSlug(), "idChap" => $notification->getRevisionComment()->getChapter()->getId()]);
                    $textChapter = "de votre feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getRevisionComment()->getChapter()->getTitle() . "</a>";
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                }
                $textSubject = "Nouveau commentaire de révision sur l'un de vos chapitres";
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getRevisionComment()->getChapter()->getPublication()->getSlug(), "id" => $notification->getRevisionComment()->getChapter()->getPublication()->getId()]);
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
                $pathPublication = $this->generateUrl('app_forum_topic_read', ['slug' => $notification->getForumMessage()->getTopic()->getCategory()->getSlug(), "id" => $notification->getForumMessage()->getTopic()->getId(), "slugTopic" => $notification->getForumMessage()->getTopic()->getSlug(), "idCom" => $notification->getForumMessage()->getId()]);
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient d'envoyer une réponse sous votre sujet de forum <a href='https://scrilab.com" . $pathPublication . "'>" . $notification->getForumMessage()->getTopic()->getTitle() . "</a>",
                        'subject' => "Nouvelle réponse sous votre sujet de forum",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 12) {
            if (is_null($userRepo->getUserParameters()->isNotif12Web()) or $userRepo->getUserParameters()->isNotif12Web() == 1) {
                $notification->setAssignForumMessage($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setAssignForumMessage($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif12Mail()) or $userRepo->getUserParameters()->isNotif12Mail() == 1) {
                $textSubject = "Nouvelle mention dans une réponse d'un sujet du forum";
                $pathPublication = $this->generateUrl('app_forum_topic_read', ['slug' => $notification->getAssignForumMessage()->getTopic()->getCategory()->getSlug(), "id" => $notification->getAssignForumMessage()->getTopic()->getId(), "slugTopic" => $notification->getAssignForumMessage()->getTopic()->getSlug(), "idCom" => $notification->getAssignForumMessage()->getId()]);
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient de vous mentionner dans une réponse sous un sujet du forum <a href='https://scrilab.com" . $pathPublication . "'>" . $notification->getAssignForumMessage()->getTopic()->getTitle() . "</a>",
                        'subject' => "Nouvelle mention dans une réponse d'un sujet du forum",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 13) {
            if (is_null($userRepo->getUserParameters()->isNotif13Web()) or $userRepo->getUserParameters()->isNotif13Web() == 1) {
                $notification->setAssignForumTopic($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setAssignForumTopic($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif13Mail()) or $userRepo->getUserParameters()->isNotif13Mail() == 1) {
                $textSubject = "Nouvelle mention dans un sujet du forum";
                $pathPublication = $this->generateUrl('app_forum_topic_read', ['slug' => $notification->getAssignForumTopic()->getCategory()->getSlug(), "id" => $notification->getAssignForumTopic()->getId(), "slugTopic" => $notification->getAssignForumTopic()->getSlug()]);
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient de vous mentionner dans son sujet du forum <a href='https://scrilab.com" . $pathPublication . "'>" . $notification->getAssignForumTopic()->getTitle() . "</a>",
                        'subject' => "Nouvelle mention dans un sujet du forum",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 14) {
            if (is_null($userRepo->getUserParameters()->isNotif14Web()) or $userRepo->getUserParameters()->isNotif14Web() == 1) {
                $notification->setAssignComment($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setAssignComment($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif14Mail()) or $userRepo->getUserParameters()->isNotif14Mail() == 1) {
                if ($notification->getAssignComment()->getChapter()) {
                    $pathChapter = $this->generateUrl('app_chapter_show', ['slugPub' => $notification->getAssignComment()->getPublication()->getSlug(), "user" => $notification->getAssignComment()->getUser()->getUsername(), "idChap" => $notification->getAssignComment()->getChapter()->getSlug(), "idCom" => $notification->getAssignComment()->getId()]);
                    $textChapter = "sur la feuille <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getAssignComment()->getChapter()->getTitle() . "</a>";
                    $showCom = false;
                } else {
                    $pathChapter = "";
                    $textChapter = "";
                    $showCom = true;
                }
                $textSubject = "Nouvelle mention dans un commentaire";
                if ($showCom) {
                    $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getAssignComment()->getPublication()->getSlug(), "id" => $notification->getAssignComment()->getPublication()->getId(), "idCom" => $notification->getAssignComment()->getId()]);
                } else {
                    $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getAssignComment()->getPublication()->getSlug(), "id" => $notification->getAssignComment()->getPublication()->getId()]);
                }
                $textPublication = ", sur le récit <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getAssignComment()->getPublication()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                    vient de vous mentionner dans un commentaire" . $textChapter . $textPublication . "<br/>
                    ",
                        'subject' => "Nouvelle mention dans un commentaire",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        // Nouvelle réponse sur l'une de vos réponses forum
        if ($type === 15) {
            if (is_null($userRepo->getUserParameters()->isNotif15Web()) or $userRepo->getUserParameters()->isNotif15Web() == 1) {
                $notification->setReplyForum($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setReplyForum($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif15Mail()) or $userRepo->getUserParameters()->isNotif15Mail() == 1) {
                $pathChapter = $this->generateUrl('app_forum_topic_read', ['slug' => $notification->getReplyForum()->getTopic()->getCategory()->getSlug(), "id" => $notification->getReplyForum()->getTopic()->getId(), "slugTopic" => $notification->getReplyForum()->getTopic()->getSlug(), "idCom" => $notification->getReplyForum()->getId()]);
                $textChapter = "au sujet de forum <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getReplyForum()->getTopic()->getTitle() . "</a>";
                //
                $textSubject = "Nouvelle réponse sous l'une de vos réponses à un sujet du forum";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient de répondre sous votre réponse " . $textChapter,
                        'subject' => "Nouvelle réponse sous l'une de vos réponses à un sujet du forum",
                    ]);
                //
                $this->mailer->send($email);
            }
        }

        if ($type === 16) {
            if (is_null($userRepo->getUserParameters()->isNotif16Web()) or $userRepo->getUserParameters()->isNotif16Web() == 1) {
                $notification->setLikeForum($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setLikeForum($idLink);
            }
            if (is_null($userRepo->getUserParameters()->isNotif16Mail()) or $userRepo->getUserParameters()->isNotif16Mail() == 1) {
                // Envoi d'email
                $pathChapter = $this->generateUrl('app_forum_topic_read', ['slug' => $notification->getLikeForum()->getTopic()->getCategory()->getSlug(), "id" => $notification->getLikeForum()->getTopic()->getId(), "slugTopic" => $notification->getLikeForum()->getTopic()->getSlug(), "idCom" => $notification->getLikeForum()->getId()]);
                $textChapter = "au sujet de forum <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getLikeForum()->getTopic()->getTitle() . "</a>";
                //
                $textSubject = "Nouveau « J'aime » sur l'un de vos réponses sur un sujet du forum";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient d'aimer votre réponse " . $textChapter,
                        'subject' => "Nouveau « J'aime » sur l'un de vos réponses sur un sujet du forum",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 17) {
            if (is_null($userRepo->getUserParameters()->isNotif17Web()) or $userRepo->getUserParameters()->isNotif17Web() == 1) {
                $notification->setAssignForumReply($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setAssignForumReply($idLink);
            }
            if (is_null($userRepo->getUserParameters()->isNotif17Mail()) or $userRepo->getUserParameters()->isNotif17Mail() == 1) {
                // Envoi d'email
                $pathChapter = $this->generateUrl('app_forum_topic_read', ['slug' => $notification->getAssignForumReply()->getTopic()->getCategory()->getSlug(), "id" => $notification->getAssignForumReply()->getTopic()->getId(), "slugTopic" => $notification->getAssignForumReply()->getTopic()->getSlug(), "idCom" => $notification->getAssignForumReply()->getId()]);
                $textChapter = "au sujet de forum <a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getAssignForumReply()->getTopic()->getTitle() . "</a>";
                //
                $textSubject = "Nouvelle mention dans une réponse sur un sujet du forum";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient de vous mentionner dans une réponse " . $textChapter,
                        'subject' => "Nouvelle mention dans une réponse sur un sujet du forum",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 18) {
            if (is_null($userRepo->getUserParameters()->isNotif18Web()) or $userRepo->getUserParameters()->isNotif18Web() == 1) {
                $notification->setNewFriend($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setNewFriend($idLink);
            }
            if (is_null($userRepo->getUserParameters()->isNotif18Mail()) or $userRepo->getUserParameters()->isNotif18Mail() == 1) {
                // Envoi d'email
                $pathChapter = $this->generateUrl('app_user', ['username' => $notification->getNewFriend()->getFromUser()->getUsername()]);
                $textChapter = "<a href='https://scrilab.com" . $pathChapter . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a> vient de s'abonner à votre profil";
                //
                $textSubject = $notification->getFromUser()->getNickname() . " vient de s'abonner à votre profil";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a> vient de s'abonner à votre profil.",
                        'subject' =>  $notification->getFromUser()->getNickname() . " vient de s'abonner à votre profil",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 19) {
            if (is_null($userRepo->getUserParameters()->isNotif19Web()) or $userRepo->getUserParameters()->isNotif19Web() == 1) {
                $notification->setFriendNewPub($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setFriendNewPub($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif19Mail()) or $userRepo->getUserParameters()->isNotif19Mail() == 1) {
                $textSubject = $notification->getFromUser()->getNickname() . " a publié un nouveau récit !";
                $pathPublication = $this->generateUrl('app_publication_show_one', ['slug' => $notification->getFriendNewPub()->getSlug(), "id" => $notification->getFriendNewPub()->getId()]);
                $textPublication = " <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getFriendNewPub()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient de publier un nouveau récit « " . $textPublication . " » ",
                        'subject' => $notification->getFromUser()->getNickname() . " a publié un nouveau récit !",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 20) {
            if (is_null($userRepo->getUserParameters()->isNotif20Web()) or $userRepo->getUserParameters()->isNotif20Web() == 1) {
                $notification->setChallengeMessage($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setChallengeMessage($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif20Mail()) or $userRepo->getUserParameters()->isNotif20Mail() == 1) {
                $textSubject = $notification->getFromUser()->getNickname() . " a commenté votre exercice";
                $pathPublication = $this->generateUrl('app_challenge_read', ['slug' => $notification->getChallengeMessage()->getChallenge()->getSlug(), "id" => $notification->getChallengeMessage()->getChallenge()->getId(), "idCom" => $notification->getChallengeMessage()->getId()]);
                $textPublication = " <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getChallengeMessage()->getChallenge()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                    a commenté votre exercice « " . $textPublication . " » ",
                        'subject' => $notification->getFromUser()->getNickname() . "  a commenté votre exercice",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 21) {
            if (is_null($userRepo->getUserParameters()->isNotif21Web()) or $userRepo->getUserParameters()->isNotif21Web() == 1) {
                $notification->setChallengeResponse($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setChallengeResponse($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif21Mail()) or $userRepo->getUserParameters()->isNotif21Mail() == 1) {
                $textSubject = $notification->getFromUser()->getNickname() . " vient de proposer une réponse à votre exercice";
                $pathPublication = $this->generateUrl('app_challenge_read', ['slug' => $notification->getChallengeResponse()->getChallenge()->getSlug(), "id" => $notification->getChallengeResponse()->getChallenge()->getId()]);
                $textPublication = " <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getChallengeResponse()->getChallenge()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vient de proposer une réponse à votre exercice « " . $textPublication . " » avec son récit «
                        <a href=\"" . $this->generateUrl('app_publication_show_one', ['id' => $notification->getChallengeResponse()->getId(), "slug" => $notification->getChallengeResponse()->getSlug()]) . "\">" . $notification->getChallengeResponse()->getTitle() . "</a>
                        »",
                        'subject' => $notification->getFromUser()->getNickname() . " vient de proposer une réponse à votre exercice",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 22) {
            if (is_null($userRepo->getUserParameters()->isNotif22Web()) or $userRepo->getUserParameters()->isNotif22Web() == 1) {
                $notification->setAssignChallenge($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setAssignChallenge($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif22Mail()) or $userRepo->getUserParameters()->isNotif22Mail() == 1) {
                $textSubject = $notification->getFromUser()->getNickname() . " vous a mentionné(e) dans l'énoncé de son exercice !";
                $pathPublication = $this->generateUrl('app_challenge_read', ['slug' => $notification->getAssignChallenge()->getSlug(), "id" => $notification->getAssignChallenge()->getId()]);
                $textPublication = " <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getAssignChallenge()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vous a mentionné(e) dans l'énoncé de son exercice « " . $textPublication . " » ",
                        'subject' => $notification->getFromUser()->getNickname() . " vous a mentionné(e) dans l'énoncé de son exercice !",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 23) {
            if (is_null($userRepo->getUserParameters()->isNotif23Web()) or $userRepo->getUserParameters()->isNotif23Web() == 1) {
                $notification->setAssignChallengeMessage($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setAssignChallengeMessage($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif23Mail()) or $userRepo->getUserParameters()->isNotif23Mail() == 1) {
                $textSubject = $notification->getFromUser()->getNickname() . " vous a mentionné(e) dans un commentaire d'exercice";
                $pathPublication = $this->generateUrl('app_challenge_read', ['slug' => $notification->getAssignChallengeMessage()->getChallenge()->getSlug(), "id" => $notification->getAssignChallengeMessage()->getChallenge()->getId(), "idCom" => $notification->getAssignChallengeMessage()->getId()]);
                $textPublication = " <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getAssignChallengeMessage()->getChallenge()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        vous a mentionné(e) dans un commentaire de l'exercice « " . $textPublication . " » ",
                        'subject' => $notification->getFromUser()->getNickname() . " vous a mentionné(e) dans un commentaire d'exercice",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 24) {
            if (is_null($userRepo->getUserParameters()->isNotif24Web()) or $userRepo->getUserParameters()->isNotif24Web() == 1) {
                $notification->setLikeChallenge($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setLikeChallenge($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif24Mail()) or $userRepo->getUserParameters()->isNotif24Mail() == 1) {
                $textSubject = $notification->getFromUser()->getNickname() . " a aimé votre commentaire sur un exercice";
                $pathPublication = $this->generateUrl('app_challenge_read', ['slug' => $notification->getLikeChallenge()->getChallenge()->getSlug(), "id" => $notification->getLikeChallenge()->getChallenge()->getId(), "idCom" => $notification->getLikeChallenge()->getId()]);
                $textPublication = " <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getLikeChallenge()->getChallenge()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        a aimé votre commentaire sur l'exercice « " . $textPublication . " » ",
                        'subject' => $notification->getFromUser()->getNickname() . " a aimé votre commentaire sur un exercice",
                    ]);
                //
                $this->mailer->send($email);
            }
        }
        if ($type === 25) {
            if (is_null($userRepo->getUserParameters()->isNotif25Web()) or $userRepo->getUserParameters()->isNotif25Web() == 1) {
                $notification->setChallengeMessageReply($idLink);
                $this->em->persist($notification);
                $this->em->flush();
            } else {
                $notification->setChallengeMessageReply($idLink);
            }
            // Envoi email
            if (is_null($userRepo->getUserParameters()->isNotif25Mail()) or $userRepo->getUserParameters()->isNotif25Mail() == 1) {
                $textSubject = $notification->getFromUser()->getNickname() . " a répondu à votre commentaire sur un exercice";
                $pathPublication = $this->generateUrl('app_challenge_read', ['slug' => $notification->getChallengeMessageReply()->getChallenge()->getSlug(), "id" => $notification->getChallengeMessageReply()->getChallenge()->getId(), "idCom" => $notification->getChallengeMessageReply()->getId()]);
                $textPublication = " <a href='https://scrilab.com" . $pathPublication . "' style='font-weight:600;'>" . $notification->getChallengeMessageReply()->getChallenge()->getTitle() . "</a>";
                $email->subject($textSubject)
                    ->context([
                        'content' => "<a href='https://scrilab.com" . $pathUserFrom . "' style='font-weight:600;'>" . $notification->getFromUser()->getNickname() . "</a>
                        a répondu à votre commentaire sur l'exercice « " . $textPublication . " » ",
                        'subject' => $notification->getFromUser()->getNickname() . " a répondu à votre commentaire sur un exercice",
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
