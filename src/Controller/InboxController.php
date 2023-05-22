<?php

namespace App\Controller;

use App\Entity\Inbox;
use DateTimeImmutable;
use App\Form\InboxType;
use App\Entity\InboxGroup;
use App\Entity\InboxGroupMember;
use App\Form\InboxNewMessageType;
use App\Form\InboxGroupMemberType;
use App\Repository\UserRepository;
use App\Repository\InboxRepository;
use App\Repository\InboxGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\InboxGroupMemberRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class InboxController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $urepo,
        private InboxRepository $inboxRepo,
        private InboxGroupMemberRepository $igmRepo,
        private InboxGroupRepository $inboxGRepo
    ) {
    }

    #[Route('/inbox', name: 'app_inbox')]
    public function index(): Response
    {
        // On redirige vers la page de création de room
        return $this->redirectToRoute('app_inbox_create');
    }
    #[Route('/inbox/create', name: 'app_inbox_create')]
    public function create(InboxRepository $inboxRepo, InboxGroupMemberRepository $igmRepo, InboxGroupRepository $inboxGRepo, Request $request, UserRepository $urepo, EntityManagerInterface $em): Response
    {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login_full');
        }
        // ! form
        $form = $this->createForm(InboxGroupMemberType::class);
        // $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (isset($data['inbox_group_member']) && isset($data['inbox_group_member']['user']) && isset($data['inbox_group_member']['user']['autocomplete'])) {
                $userIds = $data['inbox_group_member']['user']['autocomplete'];
                $users = [];
                // On crée un nom unique de room (mots réels + chiffres)
                $room = uniqid();
                // On crée cette room dans la table InboxGroup
                $inboxGroup = new InboxGroup();
                // $inboxGroup->setName($room);
                $inboxGroup->setCreatedAt(new DateTimeImmutable());
                // On persiste et flush
                $em->persist($inboxGroup);
                $em->flush();
                // ! On récupère les utilisateurs sélectionnés et on les ajoute à l'entité InboxGroupMember
                foreach ($userIds as $key => $value) {
                    if ($value != $this->getUser()->getId()) {
                        // Ici, $key est l'index dans le tableau (dans cet exemple, 0) et $value est la valeur (dans cet exemple, '1')
                        $users[] = $urepo->find($value);
                        // On ajoute l'utilisateur à l'entité InboxGroupMember associé avec l'id de la room
                        $inboxGroupMember = new InboxGroupMember();
                        $inboxGroupMember
                            ->setUser($urepo->find($value))
                            ->setUnread(0)
                            ->setGrouped($inboxGroup);
                        // On persiste et flush
                        $em->persist($inboxGroupMember);
                        $em->flush();
                    }
                }
                // On ajoute l'utilisateur courant à l'entité InboxGroupMember associé avec l'id de la room
                $inboxGroupMember = new InboxGroupMember();
                $inboxGroupMember
                    ->setUser($this->getUser())
                    ->setUnread(0)
                    ->setGrouped($inboxGroup);
                // On persiste et flush
                $em->persist($inboxGroupMember);
                $em->flush();
                // ! Création de la room dans la table InboxGroup
                // On ajoute l'utilisateur courant au tableau des utilisateurs
                $users[] = $this->getUser();
                // On vérifie qu'il n'y a pas de doublons
                $users = array_unique($users);
                // On modifie le nom de la room avec les nickname des utilisateurs, séparés par des virgules
                $room = '';
                foreach ($users as $user) {
                    $room .= $user->getUsername() . ', ';
                }
                // On supprime la dernière virgule
                $room = substr($room, 0, -2);
                // On ajoute un "et" avant le dernier nom
                $room = preg_replace('/,([^,]*)$/', ' et$1', $room);
                // On vérifie que le nom de la room n'existe pas déjà
                $inboxGroupExist = count($inboxGRepo->findBy(['name' => $room]));
                // Si la room existe déjà, on ajoute un chiffre équivalent au nombre de room existantes avec le même nom
                if ($inboxGroupExist > 0) {
                    $inboxGroupExist = $inboxGroupExist + 1;
                    $room .= ' ' . $inboxGroupExist + rand(1, 900);
                }
                // On modifie le nom de la room
                $inboxGroup->setName($room);
                // On persiste et flush
                $em->persist($inboxGroup);
                $em->flush();
                // On redirige vers la page de la room
                return $this->redirectToRoute('app_inbox_message', ['groupId' => $inboxGroup->getId()]);
            }
        }
        // On récupère les conversations
        $conversations = $igmRepo->findBy(['user' => $this->getUser()]);

        // $nbUnreadMessages = $inboxRepo->findBy(["UserTo" => $this->getUser(), "ReadAt" => null]);
        // 
        // dd($conversations);
        return $this->render('inbox/create.html.twig', [
            'conversations' => $conversations,
            // 'nbUnreadMessages' => $nbUnreadMessages,
            'formNew' => $form

        ]);
    }
    #[Route('/inbox/{groupId}', name: 'app_inbox_message', requirements: ['groupId' => '\d+'])]
    public function message(Request $request, $groupId = null): Response
    {

        // On vérifie que l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login_full');
        }
        // On récupère les infos du group
        $group = $this->inboxGRepo->find($groupId);

        // On vérifie que le groupe existe, que l'utilisateur courant est bien membre du groupe
        if (!$group || !$this->igmRepo->findOneBy(['user' => $this->getUser(), 'grouped' => $group])) {
            return $this->redirectToRoute('app_inbox');
        }
        // On récupère les conversations
        $conversations = $this->igmRepo->findBy(['user' => $this->getUser()]);

        $form = $this->createForm(InboxType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créez une nouvelle instance de PublicationTopic
            $message = $form->getData();
            // Définissez les propriétés supplémentaires
            $message->setUser($this->getUser())
                ->setGrouped($group)
                ->setCreatedAt(new DateTimeImmutable());
            // Persistez et enregistrez l'entité
            $this->em->persist($message);
            $this->em->flush();
            // On met à jour le nombre de messages non lus pour chaque membre du groupe dans InboxGroupMember
            $members = $this->igmRepo->findBy(['grouped' => $group]);
            foreach ($members as $member) {
                // On vérifie que l'utilisateur courant n'est pas le membre
                if ($member->getUser() != $this->getUser()) {
                    // On incrémente le nombre de messages non lus
                    $member->setUnread($member->getUnread() + 1);
                    // On persiste et flush
                    $this->em->persist($member);
                    $this->em->flush();
                }
            }
        }
        // On récupère les utilisateurs de la room
        $users = $this->igmRepo->findBy(['grouped' => $group]);

        return $this->render('inbox/message.html.twig', [
            'group' => $group,
            'conversations' => $conversations,
            'controller_name' => 'InboxController',
            'dateTime' => new DateTimeImmutable(),
            'form' => $form,
            'users' => $users
        ]);
    }


    #[Route('/read_at', name: 'app_inbox_message_read_at', methods: ['POST'])]
    public function ReadAt(InboxRepository $inboxRepo, InboxGroupRepository $igRepo, InboxGroupMemberRepository $igmRepo, Request $request, UserRepository $uRepo, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        $group = $data['group'];
        $user = $this->getUser();
        // On recherche le groupe
        $group = $igRepo->find($group);
        // On recherche les messages non lus
        $unread = $igmRepo->findBy(["grouped" => $group, "user" => $user]);
        // On met à jour le nombre de messages non lus à 0 pour l'utilisateur courant
        $unread[0]->setUnread(0);
        // On persiste et flush
        $em->persist($unread[0]);
        $em->flush();


        return $this->json([
            'code' => 200

        ], 200);
    }

    #[Route('/inbox/leave-group/{groupId}', name: 'app_inbox_leave_group')]
    public function leave(InboxGroupRepository $igRepo, InboxGroupMemberRepository $igmRepo, UserRepository $uRepo, EntityManagerInterface $em, $groupId = null): Response
    {
        // On récupère le nom de l'utilisateur courant :
        $user = $this->getUser();
        // On vérifie que l'utilisateur courant est bien membre du groupe
        $member = $igmRepo->findOneBy(['user' => $user, 'grouped' => $groupId]);
        if (!$member) {
            return $this->redirectToRoute('app_inbox_create');
        }
        $user = $uRepo->find($user);
        // On récupère les infos du groupe
        $group = $igRepo->find($groupId);
        // On supprime l'utilisateur courant du groupe
        $member = $igmRepo->findOneBy(['user' => $this->getUser(), 'grouped' => $group]);
        $em->remove($member);
        $em->flush();
        // On récupère les membres du groupe restants
        $members = $igmRepo->findBy(['grouped' => $group]);
        // On renomme le groupe avec les membres restants
        $name = '';
        foreach ($members as $member) {
            $name .= $member->getUser()->getNickname() . ', ';
        }
        // On supprime la dernière virgule
        $name = substr($name, 0, -2);
        // On ajoute un "et" avant le dernier nom
        $name = preg_replace('/,([^,]*)$/', ' et$1', $name);
        // On modifie le nom de la room
        $group->setName($name);
        // On persiste et flush
        $em->persist($group);
        $em->flush();
        // On ajoute un message de notification dans la room
        $message = new Inbox();
        $message
            ->setUser($user)
            ->setGrouped($group)
            ->setContent("<em>" . $user->getNickname() . ' a quitté la conversation</em>')
            ->setCreatedAt(new DateTimeImmutable());
        // On persiste et flush
        $em->persist($message);
        $em->flush();
        // On récupère les membres du groupe
        return $this->redirectToRoute('app_inbox_create');
    }
    #[Route('/inbox/remove-user/{groupId}/{userId}', name: 'app_inbox_remove_user')]
    public function remove(InboxGroupRepository $igRepo, InboxGroupMemberRepository $igmRepo, UserRepository $uRepo, EntityManagerInterface $em, $groupId = null, $userId = null): Response
    {
        // On récupère le nom de l'utilisateur courant :
        $user = $this->getUser();
        // On vérifie que l'utilisateur courant est bien membre du groupe
        $member = $igmRepo->findOneBy(['user' => $user, 'grouped' => $groupId]);
        if (!$member) {
            return $this->redirectToRoute('app_inbox_create');
        }
        $user = $uRepo->find($user);
        // On récupère les infos de userId
        $userToRemove = $uRepo->find($userId);
        // On récupère les infos du groupe
        $group = $igRepo->find($groupId);
        // On supprime l'utilisateur  du groupe
        $member = $igmRepo->findOneBy(['user' => $userToRemove, 'grouped' => $group]);
        $em->remove($member);
        $em->flush();
        // On récupère les membres du groupe restants
        $members = $igmRepo->findBy(['grouped' => $group]);
        // On renomme le groupe avec les membres restants
        $name = '';
        foreach ($members as $member) {
            $name .= $member->getUser()->getNickname() . ', ';
        }
        // On supprime la dernière virgule
        $name = substr($name, 0, -2);
        // On ajoute un "et" avant le dernier nom
        $name = preg_replace('/,([^,]*)$/', ' et$1', $name);
        // On modifie le nom de la room
        $group->setName($name);
        // On persiste et flush
        $em->persist($group);
        $em->flush();
        // On ajoute un message de notification dans la room
        $message = new Inbox();
        $message
            ->setUser($userToRemove)
            ->setGrouped($group)
            ->setContent($user->getNickname() . ' a quitté la conversation car il a été supprimé par ' . $user->getNickname())
            ->setCreatedAt(new DateTimeImmutable());
        // On persiste et flush
        $em->persist($message);
        $em->flush();
        // On récupère les membres du groupe
        return $this->redirectToRoute('app_inbox_message', ['groupId' => $groupId]);
    }
    #[Route('/inbox/delete-message/{id}/{groupId}', name: 'app_inbox_delete_message')]
    public function removeMessage(InboxGroupRepository $igRepo, InboxGroupMemberRepository $igmRepo, UserRepository $uRepo, EntityManagerInterface $em, $groupId = null, $id = null): Response
    {
        // On vérifie que l'utilisateur courant est bien l'auteur du message
        $message = $this->inboxRepo->find($id);
        if ($message->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_inbox_create');
        }
        // On modifie le contenu du message
        $message->setContent('<em>Message supprimé</em>');
        // On persiste et flush
        $em->persist($message);
        $em->flush();

        // On redirige vers la page de la room
        return $this->redirectToRoute('app_inbox_message', ['groupId' => $groupId]);
    }
}
