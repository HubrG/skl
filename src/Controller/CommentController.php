<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ForumMessage;
use App\Services\SmileyMessage;
use App\Entity\ChallengeMessage;
use App\Entity\ForumMessageLike;
use App\Entity\PublicationComment;
use App\Repository\UserRepository;
use App\Entity\ChallengeMessageLike;
use App\Services\NotificationSystem;
use App\Entity\PublicationCommentLike;
use App\Services\PublicationPopularity;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ForumMessageRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ChallengeMessageRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationCommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    private $publicationPopularity;
    private $notificationSystem;
    private $em;
    private $userRepository;

    private $smiley;
    public function __construct(SmileyMessage $smiley, UserRepository $userRepository, NotificationSystem $notificationSystem, EntityManagerInterface $em, PublicationPopularity $publicationPopularity)
    {
        $this->publicationPopularity = $publicationPopularity;
        $this->notificationSystem = $notificationSystem;
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->smiley = $smiley;
    }
    #[Route('/comment/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function CommentDelete(Request $request, EntityManagerInterface $em, PublicationCommentRepository $pcomRepo): Response
    {
        $id = $request->request->get('id');
        $forum = $request->request->get('forum');
        $challenge = $request->request->get('challenge');
        if ($forum) {
            $pcom = $this->em->getRepository(ForumMessage::class)->find($id);
        } elseif ($challenge) {
            $pcom = $this->em->getRepository(ChallengeMessage::class)->find($id);
        } else {
            $pcom = $pcomRepo->find($id);
        }
        // Si l'utilisateur est bien le propriétaire du commentaire
        if ($pcom && $pcom->getUser() == $this->getUser()) {
            $em->remove($pcom);
            $em->flush();
            //
            if (!$forum && !$challenge) {
                $this->publicationPopularity->PublicationPopularity($pcom->getPublication());
            }
            //
        } else {
            return $this->json([
                'code' => 403,
                'success' => true,
                'message' => 'Vous n\'avez pas le droit de supprimer ce commentaire'
            ], 403);
        }
        return $this->json([
            'code' => 200,
            'success' => true,
            'message' => 'Commentaire supprimé'
        ], 200);
    }
    #[Route('/comment/like', name: 'app_comment_like', methods: ['POST'])]
    public function CommentLike(Request $request, PublicationCommentRepository $pccRepo, EntityManagerInterface $em): response
    {
        $id = $request->get("id");
        $forum = $request->get("forum");
        $challenge = $request->get("challenge");
        // * On récupère le commentaire
        if ($forum) {
            $comment = $this->em->getRepository(ForumMessage::class)->find($id);
        } elseif ($challenge) {
            $comment = $this->em->getRepository(ChallengeMessage::class)->find($id);
        } else {
            $comment = $pccRepo->find($id);
        }
        // * Si le commentaire existe et que l'auteur du commentaire n'est pas l'auteur du like
        if ($comment and $comment->getUser() != $this->getUser()) {
            if ($forum) {
                $like = $comment->getForumMessageLikes()->filter(function ($like) {
                    return $like->getUser() == $this->getUser();
                })->first();
            } elseif ($challenge) {
                $like = $comment->getChallengeMessageLikes()->filter(function ($like) {
                    return $like->getUser() == $this->getUser();
                })->first();
            } else {
                $like = $comment->getPublicationCommentLikes()->filter(function ($like) {
                    return $like->getUser() == $this->getUser();
                })->first();
            }

            // * Si le commentaire a déjà été liké, on supprime le like
            if ($like) {
                $em->remove($like);
                $em->flush();
                // * On met à jour la popularité de la publication
                if (!$forum && !$challenge) {
                    $this->publicationPopularity->PublicationPopularity($comment->getPublication());
                }
                return $this->json([
                    'code' => 200,
                    'message' => 'Le like a bien été supprimé.'
                ], 200);
            }
            // * Sinon, on ajoute le like
            if ($forum) {
                $like = new ForumMessageLike();
                $like->setUser($this->getUser())
                    ->setMessage($comment)
                    ->setCreatedAt(new \DateTimeImmutable());
            } elseif ($challenge) {
                $like = new ChallengeMessageLike();
                $like->setUser($this->getUser())
                    ->setMessage($comment)
                    ->setCreatedAt(new \DateTimeImmutable());
            } else {
                $like = new PublicationCommentLike();
                $like->setUser($this->getUser())
                    ->setComment($comment)
                    ->setCreatedAt(new \DateTimeImmutable());
            }
            $em->persist($like);
            $em->flush();
            // * On met à jour la popularité de la publication
            if (!$forum && !$challenge) {
                $this->publicationPopularity->PublicationPopularity($comment->getPublication());
                // Envoi d'une notification
                $this->notificationSystem->addNotification(3, $like->getComment()->getUser(), $this->getUser(), $like);
            } else {
                // Envoi d'une notification
                if (!$forum) {
                    $this->notificationSystem->addNotification(16, $like->getMessage()->getUser(), $this->getUser(), $like);
                } else {
                    // challenge
                    // $this->notificationSystem->addNotification(17, $like->getMessage()->getUser(), $this->getUser(), $like);
                }
            }
            //
        } else {
            return $this->json([
                'code' => 403,
                'message' => 'Vous n\'avez pas les droits pour modifier ce commentaire.',
            ], 403);
        }
        //
        return $this->json([
            'code' => 201,
            'message' => 'Le like a bien été ajouté.'
        ], 201);
    }
    #[Route('/comment/update', name: 'app_comment_update', methods: ['POST'])]
    public function CommentUpdate(NotificationRepository $notifRepo, Request $request, PublicationCommentRepository $pcomRepo, EntityManagerInterface $em): response
    {
        $id = $request->get("id");
        $forum = $request->get("forum");
        $challenge = $request->get("challenge");
        $dtNewCom = $request->get("newCom");
        if ($forum) {
            $comment = $this->em->getRepository(ForumMessage::class)->find($id);
        } elseif ($challenge) {
            $comment = $this->em->getRepository(ChallengeMessage::class)->find($id);
        } else {
            $comment = $pcomRepo->find($id);
        }
        if ($comment and $comment->getUser() == $this->getUser()) {
            $comment->setContent($dtNewCom);
            $comment->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($comment);
            $em->flush();
            $content_message = $comment->getContent();
            // ! On formate les @
            $content = ' ' . $comment->getContent();
            $pattern = '/(@\w+)/';
            $content = preg_replace_callback($pattern, function ($matches) {
                $username = substr($matches[0], 1);
                $user = $this->userRepository->findOneBy(['username' => $username]);
                if ($user) {
                    $return = '<a href="/user/' . $username . '" data-turbo-frame="_top" class="text-blue-500 hover:underline">@' . $user->getNickname() . '</a>';
                    return $return;
                }
                return $matches[0];
            }, $content);
            $content = trim($content);
            // ! notification
            // On vérifie qu'il y a un ou plusieurs @ dans le message
            $pattern = '/(@\w+)/';
            $mentions = preg_match_all($pattern, $content_message, $matches);
            if ($mentions > 0) {
                // On récupère les utilisateurs mentionnés
                $mentions = $matches[0];
                foreach ($mentions as $mention) {
                    $username = substr($mention, 1);
                    $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
                    if ($user) {
                        // On vérifie que l'utilisateur n'est pas déjà mentionné dans le message dans les notifications
                        $notification = $notifRepo->findOneBy(['user' => $user, 'type' => 14, 'assignForumMessage' => $comment]);
                        $notification_forum = $notifRepo->findOneBy(['user' => $user, 'type' => 17, 'assignForumReply' => $comment]);
                        // $notification_challenge = $notifRepo->findOneBy(['user' => $user, 'type' => 17, 'assignForumReply' => $comment]);
                        if (!$forum) {
                            if (!$notification) {
                                $this->notificationSystem->addNotification(14, $user, $this->getUser(), $comment);
                            }
                        } elseif (!$challenge) {
                            // if (!$notification_challenge) {
                            //     $this->notificationSystem->addNotification(17, $user, $this->getUser(), $comment);
                            // }
                        } else {
                            if (!$notification_forum) {
                                $this->notificationSystem->addNotification(17, $user, $this->getUser(), $comment);
                            }
                        }
                    }
                }
            }
        } else {
            return $this->json([
                'code' => 403,
                'message' => 'Vous n\'avez pas les droits pour modifier ce commentaire.',
            ], 403);
        }
        //
        return $this->json([
            'code' => 200,
            'message' => 'Le commentaire a bien été modifié.',
            'comment' => $content_message, // Sans assignation
            'comment2' => $this->smiley->convertSmileyToEmoji($content) // Avec assignation
        ], 200);
    }
    #[Route('/comment/reply', name: 'app_comment_reply', methods: ['POST'])]
    public function CommentReply(NotificationRepository $notifRepo, ForumMessageRepository $fmRepo, ChallengeMessageRepository $cmRepo, Request $request, PublicationCommentRepository $pcomRepo, EntityManagerInterface $em): response
    {
        $id = $request->get("id"); // ID du commentaire principal
        $dtReplyContent = $request->get("replyContent"); // Contenu du commentaire
        $dtForum = $request->get("forum"); // Contenu du commentaire
        $dtChallenge = $request->get("challenge"); // Contenu du commentaire
        // * On cherche le commentaire principal
        if ($dtForum) {
            $commentOrigin = $fmRepo->find($id);
        } elseif ($dtChallenge) {
            $commentOrigin = $cmRepo->find($id);
        } else {
            $commentOrigin = $pcomRepo->find($id);
        }
        // * Si le commentaire existe et que l'utilisateur est connecté
        if ($commentOrigin and $this->getUser()) {
            if ($dtForum) {
                $comment = new ForumMessage();
                $comment->setUser($this->getUser())
                    ->setTopic($commentOrigin->getTopic())
                    ->setPublishedAt(new \DateTimeImmutable())
                    ->setContent($dtReplyContent)
                    ->setReplyTo($commentOrigin);
            } elseif ($dtChallenge) {
                $comment = new ChallengeMessage();
                $comment->setUser($this->getUser())
                    ->setChallenge($commentOrigin->getChallenge())
                    ->setPublishedAt(new \DateTimeImmutable())
                    ->setContent($dtReplyContent)
                    ->setReplyTo($commentOrigin);
            } else {
                $comment = new PublicationComment();
                $comment->setUser($this->getUser())
                    ->setPublication($commentOrigin->getPublication())
                    ->setPublishedAt(new \DateTimeImmutable())
                    ->setContent($dtReplyContent)
                    ->setChapter($commentOrigin->getChapter())
                    ->setReplyTo($commentOrigin);
            }
            $em->persist($comment);
            $em->flush();
            // Envoi d'une notification
            if (!$dtForum) {
                $this->notificationSystem->addNotification(9, $commentOrigin->getUser(), $this->getUser(), $comment);
            } elseif (!$dtChallenge) {
                // $this->notificationSystem->addNotification(16, $commentOrigin->getUser(), $this->getUser(), $comment);
            } else {
                $this->notificationSystem->addNotification(15, $commentOrigin->getUser(), $this->getUser(), $comment);
            }
            // ! notification
            // On vérifie qu'il y a un ou plusieurs @ dans le message
            $pattern = '/(@\w+)/';
            $mentions = preg_match_all($pattern, $comment->getContent(), $matches);
            if ($mentions > 0) {
                // On récupère les utilisateurs mentionnés
                $mentions = $matches[0];
                foreach ($mentions as $mention) {
                    $username = substr($mention, 1);
                    $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);
                    if ($user) {
                        // On vérifie que l'utilisateur n'est pas déjà mentionné dans le message dans les notifications
                        $notification = $notifRepo->findOneBy(['user' => $user, 'type' => 14, 'assignComment' => $comment]);
                        $notification_forum = $notifRepo->findOneBy(['user' => $user, 'type' => 17, 'assignForumReply' => $comment]);
                        // $notification_challenge = $notifRepo->findOneBy(['user' => $user, 'type' => 17, 'assignForumReply' => $comment]);
                        if (!$dtForum) {
                            if (!$notification) {
                                $this->notificationSystem->addNotification(14, $user, $this->getUser(), $comment);
                            }
                        } elseif (!$dtChallenge) {
                            // if (!$notification_challenge) {
                            //     $this->notificationSystem->addNotification(17, $user, $this->getUser(), $comment);
                            // }
                        } else {
                            if (!$notification_forum) {
                                $this->notificationSystem->addNotification(17, $user, $this->getUser(), $comment);
                            }
                        }
                    }
                }
            }
        } else {
            return $this->json([
                'code' => 403,
                'message' => 'Vous n\'avez pas les droits pour modifier ce commentaire.',
            ], 403);
        }
        //
        return $this->json([
            'code' => 200,
            'message' => 'Votre réponse a bien été ajoutée.',
            'comment' => $comment->getContent(),
            'commentId' => $comment->getId()
        ], 200);
    }
}
