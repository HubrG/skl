<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Notification;
use App\Form\ForumTopicType;
use App\Entity\ForumTopicRead;
use App\Entity\ForumTopicView;
use App\Form\ForumMessageType;
use App\Services\SmileyMessage;
use App\Repository\UserRepository;
use App\Services\NotificationSystem;
use App\Repository\ForumTopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ForumMessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\ForumCategoryRepository;
use App\Repository\ForumTopicReadRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForumController extends AbstractController
{
    public function __construct(
        private NotificationSystem $notificationSystem,
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private SmileyMessage $smiley
    ) {
    }
    #[Route('/forum', name: 'app_forum')]
    public function index(ForumCategoryRepository $fcRepo): Response
    {
        $categories = $fcRepo->findAll();

        return $this->render('forum/index.html.twig', [
            'category' => $categories,
        ]);
    }
    #[Route('/forum/{slug}', name: 'app_forum_topic')]
    public function topic(ForumMessageRepository $forumMessageRepository, ForumTopicRepository $ftRepo, ForumCategoryRepository $fcRepo, ForumTopicReadRepository $topicReadRepo, ForumMessageRepository $messageRepo, $slug = null): Response
    {
        $category = $fcRepo->findOneBy(['slug' => $slug]);
        if (!$category) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum');
        }


        $topics = $ftRepo->findBy(
            ['category' => $category],
            ['permanent' => 'DESC', 'createdAt' => 'DESC']
        );

        // * On récupère le nombre de derniers messages depuis la dernière visite de l'utilisateur
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if ($user) {
            // Récupérer le nombre de messages non lus pour chaque topic
            $unreadMessageCounts = [];
            foreach ($topics as $topic) {
                $unreadMessageCounts[$topic->getId()] = $forumMessageRepository->getUnreadMessageCountForUser($user, $topic);
            }
        } else {

            $unreadMessageCounts = 0;
        }

        return $this->render('forum/topics.html.twig', [
            'category' => $category,
            'topics' => $topics,
            'unreadMessageCounts' => $unreadMessageCounts,
        ]);
    }
    #[Route('/forum/create/{slug}', name: 'app_forum_topic_create')]
    public function createTopic(Request $request, ForumTopicRepository $ftRepo, SluggerInterface $slugger, EntityManagerInterface $em, ForumCategoryRepository $fcRepo, $slug = null): Response
    {
        $category = $fcRepo->findOneBy(['slug' => $slug]);
        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_forum_topic', ['slug' => $slug]);
        }
        if (!$category) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum');
        }
        $topics = $ftRepo->findBy(['category' => $category]);
        // ! form
        $form = $this->createForm(ForumTopicType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créez une nouvelle instance de PublicationTopic
            $topic = $form->getData();

            // Définissez les propriétés supplémentaires
            $topic->setCategory($category)
                ->setUser($this->getUser())
                ->setPermanent(0)
                ->setCreatedAt(new DateTimeImmutable())
                ->setSlug(strtolower($slugger->slug($topic->getTitle())));
            // Persistez et enregistrez l'entité
            $em->persist($topic);
            $em->flush();
            // ! notification
            // On vérifie qu'il y a un ou plusieurs @ dans le message
            $pattern = '/(@\w+)/';
            $content = $topic->getContent();
            $mentions = preg_match_all($pattern, $content, $matches);
            if ($mentions > 0) {
                // On récupère les utilisateurs mentionnés
                $mentions = $matches[0];
                foreach ($mentions as $mention) {
                    $username = substr($mention, 1);
                    $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
                    if ($user) {
                        // On vérifie que $user n'est pas déjà mentionné dans le message dans les notifications
                        $already = $this->em->getRepository(Notification::class)->findOneBy(['user' => $user, 'type' => 13, 'assignForumTopic' => $topic]);
                        if (!$already) {
                            $this->notificationSystem->addNotification(13, $user, $this->getUser(), $topic);
                        }
                    }
                }
            }
            // On redirige vers le topic
            return $this->redirectToRoute('app_forum_topic_read', [
                'slug' => $category->getSlug(),
                'id' => $topic->getId(),
                'slugTopic' => $topic->getSlug(),
            ]);
        }
        return $this->render('forum/create_topic.html.twig', [
            'category' => $category,
            'topics' => $topics,
            'form' => $form,
        ]);
    }
    #[Route('/forum/read/{slug}/{id}/{slugTopic}', name: 'app_forum_topic_read')]
    public function readTopic(Request $request, ForumMessageRepository $fmRepo, ForumTopicRepository $ftRepo, SluggerInterface $slugger, EntityManagerInterface $em, ForumCategoryRepository $fcRepo, $id = null, $slugTopic = null, $slug = null): Response
    {
        $category = $fcRepo->findOneBy(['slug' => $slug]);
        $nbrShowCom = 50000;
        if (!$category) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum');
        }
        $topic = $ftRepo->find($id);
        if (!$topic) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum_topic', ['slug' => $slug]);
        }
        // ! form
        $form = $this->createForm(ForumMessageType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() and $this->getUser() != null) {
            // Créez une nouvelle instance de PublicationTopic
            $message = $form->getData();
            // Définissez les propriétés supplémentaires
            $message->setTopic($topic)
                ->setUser($this->getUser())
                ->setPublishedAt(new DateTimeImmutable);
            // Persistez et enregistrez l'entité
            $em->persist($message);
            $em->flush();
            // ! notifications
            // * Envoi d'une notification de réponse au topic
            $this->notificationSystem->addNotification(11, $topic->getUser(), $this->getUser(), $message);
            // * Envoi d'une notification de mention dans le message
            // On vérifie qu'il y a un ou plusieurs @ dans le message
            $pattern = '/(@\w+)/';
            $content = $message->getContent();
            $mentions = preg_match_all($pattern, $content, $matches);
            if ($mentions > 0) {
                // On récupère les utilisateurs mentionnés
                $mentions = $matches[0];
                foreach ($mentions as $mention) {
                    $username = substr($mention, 1);
                    $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
                    if ($user) {
                        $this->notificationSystem->addNotification(12, $user, $this->getUser(), $message);
                    }
                }
            }
        }
        // * On récupère les messages du topic
        $comments = $fmRepo->findBy(['topic' => $id], ['published_at' => 'DESC'], $nbrShowCom, 0);
        $nbrCom = count($fmRepo->findBy(['topic' => $id]));
        $nbrComReal = count($fmRepo->findBy(['topic' => $id, 'replyTo' => null]));
        // * On ajoute un view pour le chapitre (si l'utilisateur n'est pas l'auteur de la publication)
        $this->viewTopic($topic);

        return $this->render('forum/read_topic.html.twig', [
            'category' => $category,
            'topic' => $topic,
            'form' => $form,
            "pCom" => $comments,
            "nbrShowCom" => $nbrShowCom,
            "nbrComReal" =>  $nbrComReal,
            "nbrCom" => $nbrCom
        ]);
    }
    #[Route('/forum/update/{id}/{slug}', name: 'app_forum_topic_update')]
    public function updateTopic(Request $request, ForumTopicRepository $ftRepo, SluggerInterface $slugger, EntityManagerInterface $em, ForumCategoryRepository $fcRepo, $slug = null, $id = null): Response
    {

        $category = $fcRepo->findOneBy(['slug' => $slug]);
        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_forum_topic', ['slug' => $slug]);
        }
        if (!$category) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum');
        }
        $topic = $ftRepo->find($id);

        if (!$topic) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum_topic', ['slug' => $slug]);
        }
        // ! form
        $form = $this->createForm(ForumTopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créez une nouvelle instance de PublicationTopic
            $topic = $form->getData();

            // Définissez les propriétés supplémentaires
            $topic->setCategory($category)
                ->setUser($this->getUser())
                ->setUpdatedAt(new DateTimeImmutable)
                ->setSlug(strtolower($slugger->slug($topic->getTitle())));
            // Persistez et enregistrez l'entité
            $em->persist($topic);
            $em->flush();
            // ! notification
            // On vérifie qu'il y a un ou plusieurs @ dans le message
            $pattern = '/(@\w+)/';
            $content = $topic->getContent();
            $mentions = preg_match_all($pattern, $content, $matches);
            if ($mentions > 0) {
                // On récupère les utilisateurs mentionnés
                $mentions = $matches[0];
                foreach ($mentions as $mention) {
                    $username = substr($mention, 1);
                    $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
                    if ($user) {
                        // On vérifie que $user n'est pas déjà mentionné dans le message dans les notifications
                        $already = $this->em->getRepository(Notification::class)->findOneBy(['user' => $user, 'type' => 13, 'assignForumTopic' => $topic]);
                        if (!$already) {
                            $this->notificationSystem->addNotification(13, $user, $this->getUser(), $topic);
                        }
                    }
                }
            }
            // On redirige vers le topic
            return $this->redirectToRoute('app_forum_topic_read', [
                'slug' => $category->getSlug(),
                'id' => $topic->getId(),
                'slugTopic' => $topic->getSlug(),
            ]);
        }
        return $this->render('forum/create_topic.html.twig', [
            'category' => $category,
            'topic' => $topic,
            'form' => $form,
        ]);
    }
    #[Route('/forum/topic/delete/{id}', name: 'app_forum_topic_delete')]
    public function deleteTopic(ForumTopicRepository $ftRepo, EntityManagerInterface $em, $id = null): Response
    {
        $topic = $ftRepo->find($id);
        // * Si l'utilisateur est bien l'auteur de la publication
        if ($this->getUser() === $topic->getUser() or $this->isGranted("ROLE_ADMIN")) {
            $em->remove($topic);
            $em->flush();
            $this->addFlash("success", "Le sujet a bien été supprimé.");
        } else {
            return $this->redirectToRoute("app_home");
        }
        return $this->redirectToRoute("app_forum_topic", ['slug' => $topic->getCategory()->getSlug()]);
    }
    #[Route('/forum/message/delete', name: 'app_forum_message_delete', methods: ['POST'])]
    public function deleteMessage(Request $request, ForumMessageRepository $fmRepo, EntityManagerInterface $em, $id = null): Response
    {

        $id = $request->request->get('id');
        $pcom = $fmRepo->find($id);

        // Si l'utilisateur est bien le propriétaire du commentaire
        if ($pcom && $pcom->getUser() == $this->getUser()) {
            $em->remove($pcom);
            $em->flush();
        } else {
            return $this->json([
                'code' => 403,
                'success' => true,
                'message' => 'Vous n\'avez pas le droit de supprimer ce commentaire'
            ], 403);
        }

        // retour en Json
        return $this->json([
            'code' => 200,
            'success' => true,
            'message' => 'Message supprimé'
        ], 200);
    }
    #[Route('/forum/message/update', name: 'app_forum_message_update', methods: ['POST'])]
    public function updateMessage(Request $request, NotificationRepository $notifRepo, ForumMessageRepository $fmRepo, EntityManagerInterface $em, $id = null): Response
    {
        $id = $request->get("id");
        $dtNewCom = $request->get("newCom");
        $message = $fmRepo->find($id);
        if ($message and $message->getUser() == $this->getUser()) {
            $message->setContent($dtNewCom);
            $message->setUpdatedAt(new DateTimeImmutable);
            $em->persist($message);
            $em->flush();
            $content_message = $message->getContent();
            // ! On formate les @
            $content = ' ' . $message->getContent();
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
                        $notification = $notifRepo->findOneBy(['user' => $user, 'type' => 12, 'assignForumMessage' => $message]);
                        if (!$notification) {
                            $this->notificationSystem->addNotification(12, $user, $this->getUser(), $message);
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
        // retour en Json
        return $this->json([
            'code' => 200,
            'success' => true,
            'message' => 'Message modifié',
            'comment' => $content_message, // Sans assignation
            'comment2' => $this->smiley->convertSmileyToEmoji($content) // Avec assignation
        ], 200);
    }

    public function viewTopic($topic)
    {
        //  ! On ajoute la vue
        $view = new ForumTopicView();
        // * SESSIONS
        if (!$this->getUser()) {
            // Récupérer la session en cours
            $session = $this->requestStack->getSession();
            if (!$session->get('viewTopic_' . $topic->getId())) {
                $session->set('viewTopic_' . $topic->getId(), true);
                $view->setTopic($topic);
                $view->setViewDate(new DateTimeImmutable);
                $this->em->persist($view);
                $this->em->flush();
            }
        }
        // * LOGGED
        if ($this->getUser()) {
            // * Si l'utilisateur n'est pas l'auteur du topic
            // ! AJOUT D'UN READ
            $readRepo = $this->em->getRepository(ForumTopicRead::class);
            $read = $readRepo->findOneBy(['user' => $this->getUser(), 'topic' => $topic]);
            // Si un read existe déjà, on le supprime et on le remplace
            if ($read) {
                $this->em->remove($read);
                $this->em->flush();
            }
            $read = new ForumTopicRead();
            $read->setTopic($topic)
                ->setUser($this->getUser())
                ->setReadAt(new DateTimeImmutable)
                ->setNbrMessage($topic->getForumMessages()->count());
            $this->em->persist($read);
            $this->em->flush();
            if ($this->getUser() != $topic->getUser()) {
                // ! AJOUT D'UNE VIEW
                // * On récupère les views liés au topic
                $viewRepo = $this->em->getRepository(ForumTopicView::class);
                // * On récupère toutes les occurrences de vue de l'utilisateur sur ce topic et on vérifie qu'il ne l'a pas vu depuis 1h
                $now = new DateTimeImmutable;
                $interval = new \DateInterval("PT1H");
                $threshold = $now->sub($interval);
                $qb = $viewRepo->createQueryBuilder('v');
                $views = $qb
                    ->where('v.user = :user')
                    ->andWhere('v.topic = :topic')
                    ->andWhere('v.viewDate >= :threshold')
                    ->setParameters([
                        'user' => $this->getUser(),
                        'topic' => $topic,
                        'threshold' => $threshold,
                    ])
                    ->getQuery()
                    ->getResult();
                // 
                // * S'il y en a on ne fait rien:
                if ($views) {
                    return;
                } else {
                    $view->setUser($this->getUser());
                }
            } else {
                return;
            }
            $view->setTopic($topic);
            $view->setViewDate(new DateTimeImmutable);
            $this->em->persist($view);
            $this->em->flush();
            //
        }
    }
}
