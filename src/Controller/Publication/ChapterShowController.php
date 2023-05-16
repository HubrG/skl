<?php

namespace App\Controller\Publication;

use DateTimeImmutable;
use PHPePub\Core\EPub;
use App\Entity\PublicationRead;
use PHPePub\Helpers\CalibreHelper;
use App\Entity\PublicationBookmark;
use App\Form\PublicationCommentType;
use App\Services\NotificationSystem;
use App\Entity\PublicationAnnotation;
use App\Entity\PublicationChapterLike;
use App\Entity\PublicationChapterNote;
use App\Entity\PublicationChapterView;
use App\Services\PublicationPopularity;
use App\Controller\AnnotationController;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\PublicationReadRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationCommentRepository;
use App\Repository\PublicationBookmarkRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\PublicationAnnotationRepository;
use App\Repository\PublicationChapterLikeRepository;
use App\Repository\PublicationChapterNoteRepository;
use App\Repository\PublicationChapterVersioningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChapterShowController extends AbstractController
{



    public function __construct(
        private AnnotationController $annotation,
        private PublicationChapterVersioningRepository $pchvRepo,
        private PublicationChapterRepository $pchRepo,
        private PublicationRepository $pRepo,
        private RequestStack $requestStack,
        private NotificationSystem $notificationSystem,
        private EntityManagerInterface $em,
        private PublicationChapterNoteRepository $chapterNote,
        private PublicationPopularity $publicationPopularity,
        private PublicationAnnotationRepository $paRepo,
    ) {
    }

    #[Route('/recit-{slugPub}/{user}/{idChap}/{slug?}/{nbrShowCom?}', name: 'app_chapter_show')]
    public function showChapter(PublicationReadRepository $pReadRepo, Request $request, PublicationCommentRepository $pcomRepo, PublicationChapterRepository $pchRepo, EntityManagerInterface $em, PublicationRepository $pRepo, $slugPub = null, $slug = null, $idChap = null, $user = null, $nbrShowCom = null): response
    {
        if (!$nbrShowCom) {
            $nbrShowCom = 10;
        }
        if (!$slug) {
            $slug = "feuille-sans-titre";
        }
        // * On recherche le chapitre
        $chapter = $pchRepo->find($idChap);
        // ! Test des conditions pour afficher le chapitre
        // * Si le chapitre existe, qu'il est publié, on récupère la publication
        if ($chapter && $chapter->getStatus() == 2) {
            $publication = $pRepo->find($chapter->getPublication());
        } else {
            // * Sinon on redirige vers la page d'acceuil
            return $this->redirectToRoute("app_home");
        }
        // * Si la publication n'est pas publiée, on redirige vers la page d'acceuil — Uniquement si l'utilisateur n'est pas l'auteur de la publication
        if ($publication->getStatus() != 2 && $publication->getUser() != $this->getUser()) {
            // On redirige vers la page précédente 
            return $this->redirectToRoute("app_home");
        }
        // * Si le chapitre n'est pas publié, on redirige vers la page d'acceuil — Uniquement si l'utilisateur n'est pas l'auteur de la publication

        elseif ($chapter->getStatus() != 2 && $publication->getUser() != $this->getUser()) {
            // On redirige vers la page précédente 
            return $this->redirectToRoute("app_home");
        }
        // ! Si toutes les conditions sont réunies, on traitre les données
        $previous = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() - 1]);
        if (!$previous) {
            $previous = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() - 2]);
        }
        $next = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() + 1]);
        if (!$next) {
            $next = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() + 2]);
        }
        $comments = $pcomRepo->findBy(['chapter' => $chapter], ['published_at' => 'DESC'], $nbrShowCom, 0);
        $nbrCom = count($pcomRepo->findBy(['chapter' => $chapter]));
        // * 
        $form = $this->createForm(PublicationCommentType::class);
        $form->handleRequest($request);
        // ON récupère le champ "quote" depuis la request
        if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {
            $quote = $request->request->get('quote');
            $comment = $form->getData();
            $comment->setChapter($chapter);
            $comment->setQuote($quote);
            $comment->setUser($this->getUser());
            $comment->setPublication($publication);
            $comment->setPublishedAt(new DateTimeImmutable());
            $em->persist($comment);
            $em->flush();
            // * On met à jour la popularité de la publication
            $this->publicationPopularity->PublicationPopularity($comment->getPublication());
            // * Envoi d'une notification
            $this->notificationSystem->addNotification(2, $comment->getPublication()->getUser(), $this->getUser(), $comment);
            //

            $this->addFlash('success', 'Votre commentaire a bien été ajouté.');
            return $this->redirectToRoute('app_chapter_show', ['slugPub' => $publication->getSlug(), 'user' => $publication->getUser()->getUsername(), 'idChap' => $chapter->getId(), 'slug' => $chapter->getSlug()]);
        } elseif ($form->isSubmitted() && !$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour pouvoir commenter.');
            return $this->redirectToRoute('app_chapter_show', ['slugPub' => $publication->getSlug(), 'user' => $publication->getUser()->getUsername(), 'idChap' => $chapter->getId(), 'slug' => $chapter->getSlug()]);
        }
        // * On vérifie si l'auteur du chapitre n'est pas l'utilisateur connecté et s'il a déjà lu le chapitre dans PublicationRead
        $current_user = $this->getUser();
        if ($current_user && $chapter->getPublication()->getUser() != $current_user) {
            $alreadyRead = $pReadRepo->findOneBy(['user' => $current_user, 'chapter' => $chapter]) ? true : null;
        } else {
            $alreadyRead = null;
        }
        // * On ajoute un view pour le chapitre (si l'utilisateur n'est pas l'auteur de la publication)
        $this->viewChapter($chapter);
        // * On formate les notes du chapitre de l'utilisateur connecté

        // !
        $version = null;
        if ($request->get('version')) {
            $version = $request->get('version');
            // On recherche la version 
            $chapterContent = $this->pchvRepo->findOneBy(['chapter' => $chapter, 'id' => $version], ['id' => 'DESC']);

            // 
            $annotation = $this->annotation->getAnnotation($chapter, "mark-for-me", $version);
            if ($annotation) {
                $chapterContent = $this->formatChapter($annotation);
            } else {
                $chapterContent = $this->formatChapter($chapterContent->getContent());
            }
        } else {
            $version = $this->pchvRepo->findOneBy(['chapter' => $chapter], ['id' => 'DESC']);
            $version = $version->getId();
            $chapterContent = $this->pchvRepo->findOneBy(['chapter' => $chapter, 'id' => $version], ['id' => 'DESC']);
            // S'il y a des annotations
            $annotation = $this->annotation->getAnnotation($chapter, "mark-for-me", $version);
            if ($annotation) {
                $chapterContent =  $this->formatChapter($annotation);
            } else {

                $chapterContent = $this->formatChapter($chapterContent->getContent());
            }
        }

        // On compte le nombre de révisions pour cette version du chapitre
        $nbrRevision = count($this->paRepo->findBy(['chapter' => $chapter, "version" => $version, 'mode' => 1]));

        // * La vue
        return $this->render('publication/show_chapter.html.twig', [
            'infoPub' => $publication,
            'infoChap' => $chapter,
            'previousChap' => $previous,
            'nextChap' => $next,
            'form' => $form,
            'formQuote' => $form,
            "pCom" => $comments,
            "version" => $version,
            "nbrShowCom" => $nbrShowCom,
            "nbrCom" => $nbrCom,
            "chapterContent" => $chapterContent,
            "canonicalUrl" => $this->generateUrl('app_chapter_show', ["slugPub" => $slugPub, "user" => $user, "idChap" => $idChap, "slug" => $slug], true),
            "alreadyRead" => $alreadyRead,
            "nbrRevision" => $nbrRevision
        ]);
    }

    #[Route('/revision/recit-{slugPub}/{user}/{idChap}/{slug}', name: 'app_chapter_revision')]
    public function showChapterRevision(PublicationAnnotationRepository $paRepo, PublicationReadRepository $pReadRepo, Request $request, PublicationCommentRepository $pcomRepo, PublicationChapterRepository $pchRepo, EntityManagerInterface $em, PublicationRepository $pRepo, $slugPub = null, $slug = null, $idChap = null, $user = null, $nbrShowCom = null): response
    {
        if (!$nbrShowCom) {
            $nbrShowCom = 10;
        }
        if (!$slug) {
            $slug = "feuille-sans-titre";
        }
        // * On recherche le chapitre
        $chapter = $pchRepo->find($idChap);
        // ! Test des conditions pour afficher le chapitre
        // * Si le chapitre existe, qu'il est publié, on récupère la publication
        if ($chapter && $chapter->getStatus() == 2) {
            $publication = $pRepo->find($chapter->getPublication());
        } else {
            // * Sinon on redirige vers la page d'acceuil
            return $this->redirectToRoute("app_home");
        }
        // * Si la publication n'est pas publiée, on redirige vers la page d'acceuil — Uniquement si l'utilisateur n'est pas l'auteur de la publication
        if ($publication->getStatus() != 2 && $publication->getUser() != $this->getUser()) {
            // On redirige vers la page précédente 
            return $this->redirectToRoute("app_home");
        }
        // * Si le chapitre n'est pas publié, on redirige vers la page d'acceuil — Uniquement si l'utilisateur n'est pas l'auteur de la publication

        elseif ($chapter->getStatus() != 2 && $publication->getUser() != $this->getUser()) {
            // On redirige vers la page précédente 
            return $this->redirectToRoute("app_home");
        }
        // ! Si toutes les conditions sont réunies, on traitre les données
        $previous = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() - 1]);
        if (!$previous) {
            $previous = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() - 2]);
        }
        $next = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() + 1]);
        if (!$next) {
            $next = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() + 2]);
        }
        $comments = $pcomRepo->findBy(['chapter' => $chapter], ['published_at' => 'DESC'], $nbrShowCom, 0);
        $nbrCom = count($pcomRepo->findBy(['chapter' => $chapter]));
        // * 
        $form = $this->createForm(PublicationCommentType::class);
        $form->handleRequest($request);
        // ON récupère le champ "quote" depuis la request
        if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {
            $quote = $request->request->get('quote');
            $comment = $form->getData();
            $comment->setChapter($chapter);
            $comment->setQuote($quote);
            $comment->setUser($this->getUser());
            $comment->setPublication($publication);
            $comment->setPublishedAt(new DateTimeImmutable());
            $em->persist($comment);
            $em->flush();
            // * On met à jour la popularité de la publication
            $this->publicationPopularity->PublicationPopularity($comment->getPublication());
            // * Envoi d'une notification
            $this->notificationSystem->addNotification(2, $comment->getPublication()->getUser(), $this->getUser(), $comment);
            //

            $this->addFlash('success', 'Votre commentaire a bien été ajouté.');
            return $this->redirectToRoute('app_chapter_show', ['slugPub' => $publication->getSlug(), 'user' => $publication->getUser()->getUsername(), 'idChap' => $chapter->getId(), 'slug' => $chapter->getSlug()]);
        } elseif ($form->isSubmitted() && !$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour pouvoir commenter.');
            return $this->redirectToRoute('app_chapter_show', ['slugPub' => $publication->getSlug(), 'user' => $publication->getUser()->getUsername(), 'idChap' => $chapter->getId(), 'slug' => $chapter->getSlug()]);
        }
        // * On vérifie si l'auteur du chapitre n'est pas l'utilisateur connecté et s'il a déjà lu le chapitre dans PublicationRead
        $current_user = $this->getUser();
        if ($current_user && $chapter->getPublication()->getUser() != $current_user) {
            $alreadyRead = $pReadRepo->findOneBy(['user' => $current_user, 'chapter' => $chapter]) ? true : null;
        } else {
            $alreadyRead = null;
        }
        // * On ajoute un view pour le chapitre (si l'utilisateur n'est pas l'auteur de la publication)
        $this->viewChapter($chapter);
        // * On formate les notes du chapitre de l'utilisateur connecté

        // !
        $version = null;
        if ($request->get('version')) {
            $version = $request->get('version');
            // On recherche la version 
            $chapterContent = $this->pchvRepo->findOneBy(['chapter' => $chapter, 'id' => $version], ['id' => 'DESC']);

            // 
            $annotation = $this->annotation->getAnnotation($chapter, "mark-for-all", $version);
            if ($annotation) {
                $chapterContent = $this->formatChapter($annotation);
            } else {
                $chapterContent = $this->formatChapter($chapterContent->getContent());
            }
        } else {
            $version = $this->pchvRepo->findOneBy(['chapter' => $chapter], ['id' => 'DESC']);

            $version = $version->getId();
            $chapterContent = $this->pchvRepo->findOneBy(['chapter' => $chapter, 'id' => $version], ['id' => 'DESC']);
            // S'il y a des annotations
            $annotation = $this->annotation->getAnnotation($chapter, "mark-for-all", $version);
            if ($annotation) {
                $chapterContent =  $this->formatChapter($annotation);
            } else {

                $chapterContent = $this->formatChapter($chapterContent->getContent());
            }
        }

        // $versions = $this->pchvRepo->findBy(['chapter' => $chapter], ['id' => 'DESC']);
        // On recherche les versions qui ont été annotées
        $chapterId = $chapter->getId();

        // Requête pour récupérer les versions avec des annotations
        $versionsWithAnnotations = $this->pchvRepo->createQueryBuilder('v1')
            ->select('v1')
            ->innerJoin('v1.chapter', 'c1')
            ->innerJoin('v1.publicationAnnotations', 'a1')
            ->where('c1.id = :chapterId1')
            ->setParameter('chapterId1', $chapterId)
            ->groupBy('v1.id')
            ->orderBy('v1.id', 'DESC')
            ->getQuery()
            ->getResult();

        // Requête pour récupérer la dernière version, indépendamment des annotations
        $lastVersion = $this->pchvRepo->createQueryBuilder('v2')
            ->select('v2')
            ->innerJoin('v2.chapter', 'c2')
            ->where('c2.id = :chapterId2')
            ->setParameter('chapterId2', $chapterId)
            ->orderBy('v2.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // Combiner les résultats et supprimer les doublons
        $versions = array_unique(array_merge($versionsWithAnnotations, [$lastVersion]), SORT_REGULAR);

        // ! On récupère le texte de la version de l'annotation
        $annotations = $this->annotation->getAnnotation($chapter, 1, $version);
        // ! On récupère toutes les annotations du chapitre de la version actuelle
        $allAnnotations = $paRepo->findBy(['chapter' => $chapter, 'mode' => 1, 'version' => $version], ['id' => 'DESC']);
        // Parcourir les annotations et les ajouter aux tableaux correspondants en fonction de leur valeur de 'color'

        $langAnnotations = [];
        $styleAnnotations = [];
        $generalAnnotations = [];
        foreach ($allAnnotations as $annotation) {
            switch ($annotation->getColor()) {
                case 1:
                    $langAnnotations[] = $annotation;
                    break;
                case 2:
                    $styleAnnotations[] = $annotation;
                    break;
                case 4:
                    $generalAnnotations[] = $annotation;
                    break;
            }
        }
        // ! reload URL
        $routeName = $request->attributes->get('_route');
        // On supprime les balises images de $chapterContent avec une regex
        $chapterContent = preg_replace('/<img[^>]*>/i', "", $chapterContent);




        // * La vue
        return $this->render('publication/show_chapter_revision.html.twig', [
            'infoPub' => $publication,
            'infoChap' => $chapter,
            "version" => $version,
            "versions" => $versions,
            "annotations" => $annotations,
            "chapterContent" => $chapterContent,
            "canonicalUrl" => $this->generateUrl('app_chapter_show', ["slugPub" => $slugPub, "user" => $user, "idChap" => $idChap, "slug" => $slug], true),
            "alreadyRead" => $alreadyRead,
            "langAnnotations" => $langAnnotations,
            "styleAnnotations" => $styleAnnotations,
            "generalAnnotations" => $generalAnnotations,

        ]);
    }

    public function viewChapter($chapter)
    {
        if ($this->getUser()) {
            // * Si l'utilisateur n'est pas l'auteur du chapitre
            if ($this->getUser() != $chapter->getPublication()->getUser()) {
                //  ! On ajoute la lecture pour la reprise
                // * On ajoute la vue du chapitre à la BDD dans PublicationRead
                $read = new PublicationRead();
                $read->setUser($this->getUser())
                    ->setReadAt(new DateTimeImmutable('now'))
                    ->setChapter($chapter)
                    ->setPublication($chapter->getPublication());
                $this->em->persist($read);
                $this->em->flush();
            }
        }
        //  ! On ajoute la vue
        $view = new PublicationChapterView();
        // * SESSIONS
        if (!$this->getUser()) {
            // Récupérer la session en cours
            $session = $this->requestStack->getSession();
            if (!$session->get('view_' . $chapter->getId())) {
                $session->set('view_' . $chapter->getId(), true);
                $view->setChapter($chapter);
                $view->setViewDate(new \DateTime('now'));
                $this->em->persist($view);
                $this->em->flush();
            }
        }
        // * LOGGED
        if ($this->getUser()) {
            // * Si l'utilisateur n'est pas l'auteur du chapitre
            if ($this->getUser() != $chapter->getPublication()->getUser()) {
                // * On récupère les views liés au chapitre
                $viewRepo = $this->em->getRepository(PublicationChapterView::class);
                // * On récupère toutes les occurrences de vue de l'utilisateur sur ce chapitre et on vérifie qu'il ne l'a pas vu depuis 1h
                $now = new \DateTime();
                $interval = new \DateInterval("PT1H");
                $now->sub($interval);
                $qb = $viewRepo->createQueryBuilder('v');
                $views = $qb
                    ->where('v.user = :user')
                    ->andWhere('v.chapter = :chapter')
                    ->andWhere('v.view_date >= :now')
                    ->setParameters([
                        'user' => $this->getUser(),
                        'chapter' => $chapter,
                        'now' => $now,

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
            $view->setChapter($chapter);
            $view->setViewDate(new \DateTime('now'));
            $this->em->persist($view);
            $this->em->flush();
            //
        }
        // * On met à jour la popularité de la publication
        $this->publicationPopularity->PublicationPopularity($chapter->getPublication());
    }
    /**
     * Met en forme le contenu d'un chapitre en effectuant les opérations suivantes :
     *
     * 1. Remplacer les balises <div> par des balises <p>.
     * 2. Ajouter un attribut id unique à chaque balise <p>.
     * 3. Ajuster les URL des images pour un redimensionnement et une mise à l'échelle automatiques.
     *
     * @param object $chapter L'objet chapitre contenant le contenu à formater
     *
     * @return string Le contenu formaté
     */
    public function formatChapter($content)
    {
        $id = 0;


        // Remplace les balises <div> par des balises <p>
        $content = preg_replace('/<div\s*(.*?)>(.*?)<\/div>/', '<p $1>$2</p>', $content);

        // Ajoute un attribut id aux balises <p>
        $newText = preg_replace_callback('/<p\s*(.*?)>(.*?)<\/p>/', function ($matches) use (&$id) {
            return '<p id="paragraphe-' . $id++ . '" ' . $matches[1] . '>' . $matches[2] . '</p>';
        }, $content);

        // Modifie les URL des images
        $newText = str_replace('/image/upload/', '/image/upload/w_auto,c_scale/', $newText);

        return $newText;
    }






    #[Route('/recit/chapter/like', name: 'app_chapter_like', methods: ['POST'])]
    public function Axios_ChapterLike(Request $request, PublicationChapterRepository $pchRepo, PublicationChapterLikeRepository $pclRepo, EntityManagerInterface $em): response
    {
        $pch = $pchRepo->find($request->get("idChapter"));
        if (!$pch || !$this->getUser() || $pch->getPublication()->getUser() == $this->getUser()) {
            return $this->json([
                'code' => 200,
                'nbrLike' => $pclRepo->count(['chapter' => $pch]),
                'message' => 'Erreur',
            ], 200);
        }
        $like = $pclRepo->findOneBy(['chapter' => $pch, 'user' => $this->getUser()]);
        if ($like) {
            $em->remove($like);
            $em->flush();
            // * On met à jour la popularité de la publication
            $this->publicationPopularity->PublicationPopularity($pch->getPublication());
            //
            return $this->json([
                'code' => 200,
                'resp' => false,
                'nbrLike' => $pclRepo->count(['chapter' => $pch]),
                'message' => 'Le like a bien été supprimée.',
            ], 200);
        }

        $like = new PublicationChapterLike();
        $like->setChapter($pch);
        $like->setUser($this->getUser());
        $like->setCreatedAt(new DateTimeImmutable('now'));
        $em->persist($like);
        $em->flush();
        // * On met à jour la popularité de la publication
        $this->publicationPopularity->PublicationPopularity($pch->getPublication());
        // * Envoi d'une notification
        $this->notificationSystem->addNotification(6, $like->getChapter()->getPublication()->getUser(), $this->getUser(), $like);
        //
        return $this->json([
            'code' => 200,
            'resp' => true,
            'nbrLike' => $pclRepo->count(['chapter' => $pch]),
            'message' => 'Le chapitre a bien été liké.',
        ], 200);
    }

    #[Route('/recit/chapter/bm', name: 'app_chapter_bm', methods: ['POST'])]
    public function Axios_ChapterBm(Request $request, PublicationChapterRepository $pchRepo, PublicationBookmarkRepository $pbRepo, EntityManagerInterface $em): response
    {
        $pch = $pchRepo->find($request->get("idChapter"));
        if (!$pch || !$this->getUser() || $pch->getPublication()->getUser() == $this->getUser()) {
            return $this->json([
                'code' => 200,
                'nbrBm' => $pbRepo->count(['chapter' => $pch]),
                'message' => 'Non autorisé.',
            ], 200);
        }
        $bm = $pbRepo->findOneBy(['chapter' => $pch, 'user' => $this->getUser()]);
        if ($bm) {
            $em->remove($bm);
            $em->flush();
            // * On met à jour la popularité de la publication
            $this->publicationPopularity->PublicationPopularity($pch->getPublication());
            //
            return $this->json([
                'code' => 200,
                'resp' => false,
                'nbrBm' => $pbRepo->count(['chapter' => $pch]),
                'message' => 'Le chapitre a bien été supprimée des bookmarks.',
            ], 200);
        }

        $bm = new PublicationBookmark();
        $bm->setChapter($pch);
        $bm->setUser($this->getUser());
        $bm->setCreatedAt(new DateTimeImmutable('now'));
        $em->persist($bm);
        $em->flush();
        // * On met à jour la popularité de la publication
        $this->publicationPopularity->PublicationPopularity($pch->getPublication());
        // * Envoi d'une notification
        $this->notificationSystem->addNotification(4, $pch->getPublication()->getUser(), $this->getUser(), $bm);
        //
        return $this->json([
            'code' => 200,
            'resp' => true,
            'nbrBm' => $pbRepo->count(['chapter' => $pch]),
            'message' => 'Le chapitre a bien été ajouté aux bookmarks',
        ], 200);
    }

    #[Route('/download_epub/{id?}', name: 'download_epub')]
    public function downloadEpub($id = null)
    {

        // On recherche la publication via l'ID
        $publication = $this->pRepo->find($id);
        //

        $content_start =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
            . "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n"
            . "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n"
            . "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n"
            . "<head>"
            . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n"
            . "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n"
            . "<title>" . $publication->getTitle() . "</title>\n"
            . "</head>\n"
            . "<body>\n";

        $bookEnd = "</body>\n</html>\n";


        $book = new EPub(); // no arguments gives us the default ePub 2, lang=en and dir="ltr"

        // Title and Identifier are mandatory!
        $book->setTitle($publication->getTitle());
        // $book->setIdentifier("http://JohnJaneDoePublications.com/books/TestBookSimple.html", EPub::IDENTIFIER_URI); // Could also be the ISBN number, preferrd for published books, or a UUID.
        $book->setLanguage("fr"); // Not needed, but included for the example, Language is mandatory, but EPub defaults to "en". Use RFC3066 Language codes, such as "en", "da", "fr" etc.
        $book->setDescription($publication->getSummary());
        $book->setAuthor($publication->getUser()->getNickname(), $publication->getUser()->getNickname());
        $book->setPublisher("Scrilab", "https://scrilab.com"); // I hope this is a non existent address :)
        $book->setDate(time()); // Strictly not needed as the book date defaults to time().
        // $book->setRights("Copyright and licence information specific for the book."); // As this is generated, this _could_ contain the name or licence information of the user who purchased the book, if needed. If this is used that way, the identifier must also be made unique for the book.
        $book->setSourceURL($this->generateUrl('app_publication_show_one', ['id' => $publication->getId(), 'slug' => $publication->getSlug(), 'nbrShowCom' => 10])); // Sets the link to the book's source on the web.

        // Insert custom meta data to the book, in this case, Calibre series index information.
        CalibreHelper::setCalibreMetadata($book, "Scrilab Ebook", "5");

        // A book need styling, in this case we use static text, but it could have been a file.
        $cssData = "body {\n  margin-left: .5em;\n  margin-right: .5em;\n  text-align: justify;\n}\n\np {\n  font-family: serif;\n  font-size: 10pt;\n  text-align: justify;\n  text-indent: 1em;\n  margin-top: 0px;\n  margin-bottom: 1ex;\n}\n\nh1, h2 {\n  font-family: sans-serif;\n  font-style: italic;\n  text-align: center;\n  background-color: #6b879c;\n  color: white;\n  width: 100%;\n}\n\nh1 {\n    margin-bottom: 2px;\n}\n\nh2 {\n    margin-top: -2px;\n    margin-bottom: 2px;\n}\n";
        $book->addCSSFile("styles.css", "css1", $cssData);

        // Add cover page
        $cover = $content_start . "<h1>" . $publication->getTitle() . "</h1>\n<h2>" . $publication->getUser()->getNickname() . "</h2>\n" . $bookEnd;
        $book->addChapter("Notices", "Cover.html", $cover);
        // On récupère le contenu de chaque chapitre avec le status 2
        $chapters = $this->pchRepo->findBy(['publication' => $publication, 'status' => 2], ['order_display' => 'ASC']);
        // On récupère le contenu de chacun d'entre eux
        foreach ($chapters as $chapter) {
            $rawContent = $chapter->getContent();
            // Suppression des attributs des balises p, h1, h2, h3, h4 etc.
            $cleanContent = preg_replace('/(<(p|h1|h2|h3|h4)[^>]*>)/i', '<$2>', $rawContent);
            $cleanContent = $rawContent;
            $content = $content_start . "<h1>" . $chapter->getTitle() . "</h1>\n" . $cleanContent . $bookEnd;
            $book->addChapter($chapter->getTitle(), $chapter->getSlug() . '.html', $content, true, EPub::EXTERNAL_REF_ADD);
        }

        $book->finalize(); // Finalize the book, and build the archive.

        // Send the book to the client. ".epub" will be appended if missing.
        $zipData = $book->sendBook($publication->getTitle() . " - " . $publication->getUser()->getNickname());
        return $zipData;
    }
}
