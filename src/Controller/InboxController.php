<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Form\InboxType;
use App\Form\InboxNewMessageType;
use App\Repository\UserRepository;
use App\Repository\InboxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class InboxController extends AbstractController
{
    #[Route('/inbox', name: 'app_inbox')]
    public function index(InboxRepository $inboxRepo, Request $request, EntityManagerInterface $em): Response
    {

        $conversations = $inboxRepo->createQueryBuilder('m')
            ->where('m.user = :user')
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->getResult();



        $conversations2 = $inboxRepo->createQueryBuilder('m')
            ->where('m.UserTo = :user')
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->getResult();

        // ON merge
        $conversations = array_merge($conversations, $conversations2);





        // dd($conversations);
        // NOmbre total de messages non lus
        $nbUnreadMessages = $inboxRepo->findBy(["UserTo" => $this->getUser(), "ReadAt" => null]);

        return $this->render('inbox/index.html.twig', [
            'conversations' => $conversations,
            'nbUnreadMessages' => $nbUnreadMessages,

        ]);
    }
    #[Route('/inbox/create', name: 'app_inbox_create')]
    public function create(InboxRepository $inboxRepo, Request $request, EntityManagerInterface $em): Response
    {

        $conversations = $inboxRepo->findDistinctUserToByUser($this->getUser());
        $nbUnreadMessages = $inboxRepo->findBy(["UserTo" => $this->getUser(), "ReadAt" => null]);
        // 
        // ! form
        $form = $this->createForm(InboxNewMessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créez une nouvelle instance de PublicationTopic
            $message = $form->getData();

            // Définissez les propriétés supplémentaires
            $message->setUser($this->getUser())
                ->setCreatedAt(new DateTimeImmutable());
            // Persistez et enregistrez l'entité
            $em->persist($message);
            $em->flush();
            return $this->redirectToRoute('app_inbox_message', ['userTo' => $message->getUserTo()->getId()]);
        }
        //  
        return $this->render('inbox/create.html.twig', [
            'conversations' => $conversations,
            'nbUnreadMessages' => $nbUnreadMessages,
            'formNew' => $form

        ]);
    }
    #[Route('/inbox/{userTo}', name: 'app_inbox_message', requirements: ['userTo' => '\d+'])]
    public function message(InboxRepository $inboxRepo, Request $request, UserRepository $uRepo, EntityManagerInterface $em, $userTo = null): Response
    {
        // On recherche l'utilisateur
        $userTo = $uRepo->find($userTo);

        // $conversations = $inboxRepo->findDistinctUserToByUser($this->getUser());

        $conversations = $inboxRepo->findBy([
            'user' => $this->getUser(),
            'UserTo' => $userTo,
        ]);

        // ! form
        $form = $this->createForm(InboxType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créez une nouvelle instance de PublicationTopic
            $message = $form->getData();

            // Définissez les propriétés supplémentaires
            $message->setUser($this->getUser())
                ->setUserTo($userTo)
                ->setCreatedAt(new DateTimeImmutable());
            // Persistez et enregistrez l'entité
            $em->persist($message);
            $em->flush();
            // On redirige vers le topic
            // return $this->render('inbox/index.html.twig', [
            //     'slug' => $category->getSlug(),
            //     'id' => $topic->getId(),
            //     'slugTopic' => $topic->getSlug(),
            // ]);
        }
        // On reprend tous les messages
        $messagesSentByUser = $inboxRepo->findBy([
            'user' => $this->getUser(),
            'UserTo' => $userTo,
        ]);

        // Récupérez les messages envoyés par l'utilisateur userTo à l'utilisateur User
        $messagesSentByUserTo = $inboxRepo->findBy([
            'user' => $userTo,
            'UserTo' => $this->getUser(),
        ]);

        // Fusionnez les deux tableaux de messages
        $allMessages = array_merge($messagesSentByUser, $messagesSentByUserTo);

        // Triez les messages par date, si nécessaire
        usort($allMessages, function ($messageA, $messageB) {
            return $messageA->getCreatedAt() <=> $messageB->getCreatedAt();
        });

        // NOmbre total de messages non lus
        $nbUnreadMessages = $inboxRepo->findBy(["UserTo" => $this->getUser(), "ReadAt" => null]);

        return $this->render('inbox/message.html.twig', [
            'controller_name' => 'InboxController',
            'form' => $form,
            'messages' => $allMessages,
            'userTo' => $userTo,
            'conversations' => $conversations,
            'nbUnreadMessages' => $nbUnreadMessages,
        ]);
    }
    #[Route('/inbox/search_user', name: 'app_inbox_search_user', methods: ['POST'])]
    public function searchUser(Request $request, UserRepository $uRepo): Response
    {
        $data = json_decode($request->getContent(), true);

        $qb = $uRepo->createQueryBuilder('u');

        $qb->where('(u.username LIKE :term) or (u.nickname LIKE :term)')
            ->setParameter('term', '%' . $data["searchUser"] . '%')
            ->setMaxResults(10); // Limite le nombre de résultats (facultatif)

        $return = $qb->getQuery()->getResult();

        $users = [];
        foreach ($return as $user) {
            $users[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'nickname' => $user->getNickname(),
                'avatar' => $user->getProfilPicture(),
            ];
        }
        return $this->json([
            'code' => 200,
            'users' => $users
        ], 200);
    }
    #[Route('/reload-inbox', name: 'app_inbox_message_reload', methods: ['POST'])]
    public function reloadMessage(InboxRepository $inboxRepo, SerializerInterface $serializer, Request $request, UserRepository $uRepo, EntityManagerInterface $em, $userTo = null): Response
    {
        $data = json_decode($request->getContent(), true);

        $userTo = $data['userTo'];
        $lastMessage = $data['lastMessage'];
        $lastMessageDate = DateTimeImmutable::createFromFormat('F j, Y H:i:s', $lastMessage);
        // Vérifiez si la conversion a réussi
        if ($lastMessageDate === false) {
            throw new \Exception("La date fournie n'a pas pu être convertie en un objet DateTimeImmutable.");
        }


        // S'il existe une entrée avec une date supérieure à celle de $lastMessage, on renvoie true
        $reload = $inboxRepo->createQueryBuilder('m')
            ->where('(m.user = :userTo AND m.UserTo = :currentUser) OR (m.user = :currentUser AND m.UserTo = :userTo)')
            ->andWhere('m.CreatedAt > :lastMessageDate')
            ->setParameters([
                'userTo' => $userTo,
                'currentUser' => $this->getUser(),
                'lastMessageDate' => $lastMessageDate,
            ])
            ->getQuery()
            ->getResult();


        $reload = $reload ? true : false;

        // On compte le nombre de nouveaux messages
        $nbUnreadMessages = $inboxRepo->findBy(["UserTo" => $this->getUser(), "ReadAt" => null]);
        // On crée un nouveau tableau avec le nombre de nouveaux messages par utilisateur (user)
        $nbUnreadMessagesByUser = [];
        foreach ($nbUnreadMessages as $message) {
            $user = $message->getUser()->getId();
            if (!array_key_exists($user, $nbUnreadMessagesByUser)) {
                $nbUnreadMessagesByUser[$user] = 1;
            } else {
                $nbUnreadMessagesByUser[$user]++;
            }
        }

        return $this->json([
            'code' => 200,
            'message' => $reload,
            'nbUnreadMessages' => $nbUnreadMessagesByUser,
        ], 200);
    }
    #[Route('/read_at', name: 'app_inbox_message_read_at', methods: ['POST'])]
    public function ReadAt(InboxRepository $inboxRepo, Request $request, UserRepository $uRepo, EntityManagerInterface $em, $userTo = null): Response
    {
        $data = json_decode($request->getContent(), true);

        $userTo = $data['userTo'];
        $user = $this->getUser();

        // On recherche UserTo
        $userTo = $uRepo->find($userTo);

        // On recherche les messages non lus et on leur attribue un readAt = now en Datetimeimmutable
        $messages = $inboxRepo->createQueryBuilder('m')
            ->where('m.UserTo = :currentUser AND m.user = :user')
            ->andWhere('m.ReadAt IS NULL')
            ->setParameters([
                'user' => $userTo,
                'currentUser' => $user,
            ])
            ->getQuery()
            ->getResult();
        // On boucle sur les messages et on leur attribue un readAt
        foreach ($messages as $message) {
            $message->setReadAt(new DateTimeImmutable());
            $em->persist($message);
        }
        $em->flush();

        return $this->json([
            'code' => 200

        ], 200);
    }
}
