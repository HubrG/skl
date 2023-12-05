<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Publication;
use App\Form\ChallengeType;
use App\Entity\Notification;
use App\Form\ForumTopicType;
use App\Services\SmileyMessage;
use App\Form\ChallengeMessageType;
use App\Repository\UserRepository;
use App\Services\NotificationSystem;
use App\Repository\ChallengeRepository;
use App\Repository\ForumTopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\ForumCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ChallengeMessageRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChallengeController extends AbstractController
{

    public function __construct(
        private NotificationSystem $notificationSystem,
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private SmileyMessage $smiley
    ) {
    }

    #[Route('/exercice', name: 'app_challenge')]
    public function index(ChallengeRepository $cRepo): Response
    {
        $dateActuelle = new DateTime();

        $challenges = $cRepo->createQueryBuilder("c")
            ->addSelect('CASE 
                WHEN c.dateEnd IS NULL THEN 0 
                WHEN c.dateEnd > :dateActuelle THEN 0 
                ELSE 1 
            END AS HIDDEN ORD')
            ->setParameter('dateActuelle', $dateActuelle)
            ->orderBy('ORD', 'ASC')
            ->addOrderBy("c.dateEnd", "ASC")
            ->addOrderBy("c.createdAt", "DESC")
            ->getQuery()
            ->getResult();


        return $this->render('challenge/index.html.twig', [
            "challenges" => $challenges
        ]);
    }
    #[Route('/exercice/creer/{slug}', name: 'app_challenge_create')]
    public function createChallenge(Request $request, ChallengeRepository $chRepo, SluggerInterface $slugger, EntityManagerInterface $em, $slug = null): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_challenge');
        }
        // ! form
        $form = $this->createForm(ChallengeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créez une nouvelle instance de PublicationTopic
            $challenge = $form->getData();
            // Définissez les propriétés supplémentaires
            $challenge
                ->setUser($this->getUser())
                ->setContest(0)
                ->setCreatedAt(new DateTimeImmutable())
                ->setSlug(strtolower($slugger->slug($challenge->getTitle())));
            // Persistez et enregistrez l'entité
            $em->persist($challenge);
            $em->flush();
            // ! notification
            // On vérifie qu'il y a un ou plusieurs @ dans le message
            $pattern = '/(@\w+)/';
            $content = $challenge->getContent();
            $mentions = preg_match_all($pattern, $content, $matches);
            if ($mentions > 0) {
                // On récupère les utilisateurs mentionnés
                $mentions = $matches[0];
                foreach ($mentions as $mention) {
                    $username = substr($mention, 1);
                    $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
                    if ($user) {
                        // * On vérifie que $user n'est pas déjà mentionné dans le message dans les notifications
                        $already = $this->em->getRepository(Notification::class)->findOneBy(['user' => $user, 'type' => 22, 'assignChallenge' => $challenge]);
                        if (!$already) {
                            $this->notificationSystem->addNotification(22, $user, $this->getUser(), $challenge);
                        }
                    }
                }
            }
            // On redirige vers le challenge
            $this->addFlash("success", "L'atelier a bien été créé.");
            return $this->redirectToRoute('app_challenge_read', [
                'id' => $challenge->getId(),
                'slug' => $challenge->getSlug(),
            ]);
        }
        return $this->render('challenge/create_challenge.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/exercice/lire/{id}/{slug}', name: 'app_challenge_read')]
    public function readChallenge(Request $request, PublicationRepository $pRepo, ChallengeRepository $cRepo, ChallengeMessageRepository $cmRepo, EntityManagerInterface $em, $id = null, $slug = null): Response
    {
        $nbrShowCom = 50000;
        $challenge = $cRepo->find($id);
        if (!$challenge) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_challenge');
        }
        // ! form
        $form = $this->createForm(ChallengeMessageType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() and $this->getUser() != null) {
            // Créez une nouvelle instance de PublicationChallenge
            $message = $form->getData();
            // Définissez les propriétés supplémentaires
            $message->setChallenge($challenge)
                ->setUser($this->getUser())
                ->setPublishedAt(new DateTimeImmutable);
            // Persistez et enregistrez l'entité
            $em->persist($message);
            $em->flush();
            // ! notifications
            // * Envoi d'une notification de réponse au topic
            $this->notificationSystem->addNotification(20, $challenge->getUser(), $this->getUser(), $message);
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
                        $this->notificationSystem->addNotification(23, $user, $this->getUser(), $message);
                    }
                }
            }
        }
        // * On récupère les messages du challenge
        $comments = $cmRepo->findBy(['challenge' => $id], ['publishedAt' => 'DESC'], $nbrShowCom, 0);
        $nbrCom = count($cmRepo->findBy(['challenge' => $id]));
        $nbrComReal = count($cmRepo->findBy(['challenge' => $id, 'replyTo' => null]));
        // * On récupère les exercices réalisés qui ont au moins un chapitre publié
        // * DERNIÈRES PUBLICATIONS
        $user = $challenge->getUser();

        $qb = $pRepo->createQueryBuilder("p")
            ->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
            ->where("p.status = 2")
            ->andWhere("p.challenge = :challenge")
            ->addSelect('CASE 
        WHEN p.user = :user THEN 0
        ELSE 1 
    END AS HIDDEN ORD')
            ->orderBy('ORD', 'ASC')
            ->addOrderBy("p.published_date", "DESC")
            ->groupBy('p.id')
            ->setParameter('user', $user)
            ->setParameter('challenge', $challenge);

        $challenges = $qb->getQuery()->getResult();

        // * DERNIÈRES PUBLICATIONS DRAFTED
        // Sous-requête pour obtenir les id des publications qui ont au moins un chapitre publié
        // Sous-requête pour obtenir les id des publications qui ont au moins un chapitre publié
        $subQuery = $em->createQueryBuilder()
            ->select('p2.id')
            ->from('App:Publication', 'p2')
            ->innerJoin('p2.publicationChapters', 'pch2')
            ->where('pch2.status >= 2');

        // Requête principale
        $qb = $pRepo->createQueryBuilder("p")
            ->where('p.challenge = :challenge')
            ->andWhere('p.id NOT IN (' . $subQuery->getDQL() . ')')
            ->andWhere('p.user = :user')
            ->orderBy('p.created', 'DESC')
            ->setParameter('challenge', $challenge)
            ->setParameter('user', $this->getUser());


        $challenges_draft = $qb->getQuery()->getResult();




        return $this->render('challenge/read_challenge.html.twig', [
            "challenge" => $challenge,
            'form' => $form,
            "pCom" => $comments,
            "nbrShowCom" => $nbrShowCom,
            "nbrComReal" => $nbrComReal,
            "nbrCom" => $nbrCom,
            "challenges" => $challenges,
            "challenges_draft" => $challenges_draft,
        ]);
    }
    #[Route('/exercice/modifier/{id}/{slug}', name: 'app_challenge_update')]
    public function updateChallenge(Request $request, PublicationRepository $pRepo, ChallengeRepository $cRepo, SluggerInterface $slugger, EntityManagerInterface $em, ForumCategoryRepository $fcRepo, $slug = null, $id = null): Response
    {


        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_challenge');
        }

        $challenge = $cRepo->find($id);

        if (!$challenge) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_challenge');
        }
        // ! form
        $form = $this->createForm(ChallengeType::class, $challenge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créez une nouvelle instance de PublicationTopic
            $challenge = $form->getData();

            // Définissez les propriétés supplémentaires
            $challenge->setUser($this->getUser())
                ->setUpdatedAt(new DateTime("now"))
                ->setSlug(strtolower($slugger->slug($challenge->getTitle())));
            // Persistez et enregistrez l'entité
            $em->persist($challenge);
            $em->flush();
            // ! notification
            // On vérifie qu'il y a un ou plusieurs @ dans le message
            $pattern = '/(@\w+)/';
            $content = $challenge->getContent();
            $mentions = preg_match_all($pattern, $content, $matches);
            if ($mentions > 0) {
                // On récupère les utilisateurs mentionnés
                $mentions = $matches[0];
                foreach ($mentions as $mention) {
                    $username = substr($mention, 1);
                    $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
                    if ($user) {
                        // On vérifie que $user n'est pas déjà mentionné dans le message dans les notifications
                        $already = $this->em->getRepository(Notification::class)->findOneBy(['user' => $user, 'type' => 22, 'assignChallenge' => $challenge]);
                        if (!$already) {
                            $this->notificationSystem->addNotification(22, $user, $this->getUser(), $challenge);
                        }
                    }
                }
            }
            // On redirige vers le challenge
            $this->addFlash("success", "L'atelier a bien été modifié.");
            return $this->redirectToRoute('app_challenge_read', [
                'slug' => $challenge->getSlug(),
                'id' => $challenge->getId(),
            ]);
        }
        return $this->render('challenge/create_challenge.html.twig', [
            'challenge' => $challenge,
            'form' => $form,
        ]);
    }
    #[Route('/challenge/delete/{id}', name: 'app_challenge_delete')]
    public function deleteChallenge(ChallengeRepository $cRepo, EntityManagerInterface $em, $id = null): Response
    {
        $challenge = $cRepo->find($id);
        // * Si l'utilisateur est bien l'auteur du challenge
        if ($this->getUser() === $challenge->getUser() or $this->isGranted("ROLE_ADMIN")) {
            $em->remove($challenge);
            $em->flush();
            $this->addFlash("success", "L'atelier a bien été supprimé.");
        } else {
            return $this->redirectToRoute("app_home");
        }
        return $this->redirectToRoute("app_challenge");
    }
    #[Route('/challenge/reply/{id}', name: 'app_challenge_reply')]
    public function replyChallenge(ChallengeRepository $cRepo, EntityManagerInterface $em, SluggerInterface $slugger, $id = null): Response
    {
        // On recherche le challenge 
        $challenge = $cRepo->find($id);
        if (!$challenge) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_challenge');
        }
        // Si le challenge a une dateEnd et que cette date est dépassée, on ne peut pas répondre
        if ($challenge->getDateEnd() != null and $challenge->getDateEnd() < new DateTime("now")) {
            $this->addFlash("error", "La date limite pour répondre à cet atelier est dépassée.");
            return $this->redirectToRoute("app_challenge_read", [
                "id" => $id
            ]);
        }
        // * If user is connected
        if ($this->getUser()) {
            // * We get our last draft, if it exists
            $publication = new Publication();
            $publication->setUser($this->getUser());
            $publication->setStatus(1);
            $publication->setTitle("Réponse à l'atelier / " . $challenge->getTitle());
            $publication->setAccess(0);
            $publication->setSlug($slugger->slug(strtolower("Réponse à l'atelier / " . $challenge->getTitle())));
            $publication->setHideSearch(0);
            $publication->setSupport(0);
            $publication->setAllowRevision(1);
            $publication->setShowOldVersions(1);
            $publication->setType(0);
            $publication->setMature(0);
            $publication->setCreated(new DateTime("now"));
            $publication->setChallenge($challenge);
            if ($challenge->getConstrainCategory()) {
                $publication->setCategory($challenge->getConstrainCategory());
            }
            $em->persist($publication);
            $em->flush();
            return $this->redirectToRoute("app_publication_edit", [
                "id" => $publication->getId()
            ]);
        } else {
            $this->addFlash("error", "Vous devez être connecté pour accéder à cette page.");
            return $this->redirectToRoute("app_challenge_read", [
                "id" => $id
            ]);
        }
    }
}
