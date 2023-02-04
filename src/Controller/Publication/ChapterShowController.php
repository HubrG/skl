<?php

namespace App\Controller\Publication;

use Symfony\Bundle\SessionBundle;
use App\Entity\PublicationChapterNote;
use App\Entity\PublicationChapterView;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Form\PublicationChapterCommentType;
use Symfony\Component\HttpFoundation\Cookie;
use App\Entity\PublicationChapterCommentLike;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationChapterNoteRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Repository\PublicationChapterCommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChapterShowController extends AbstractController
{

    private $em;
    private $chapterNote;

    public function __construct(EntityManagerInterface $em, PublicationChapterNoteRepository $chapterNote)
    {
        $this->em = $em;
        $this->chapterNote = $chapterNote;
    }

    #[Route('/recit-{slugPub}/{user}/{idChap}/{slug?}/{nbrShowCom?}', name: 'app_chapter_show')]
    public function showChapter(Request $request, PublicationChapterCommentRepository $pccRepo,  PublicationChapterRepository $pchRepo, EntityManagerInterface $em, PublicationRepository $pRepo, $slug = null, $idChap = null, $nbrShowCom = null): response
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
        // * Si le chapitre existe, on récupère la publication
        if ($chapter) {
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
        $next = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() + 1]);
        $comments = $pccRepo->findBy(['chapter' => $chapter], ['publish_date' => 'DESC'], $nbrShowCom, 0);
        // * 
        $form = $this->createForm(PublicationChapterCommentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setChapter($chapter);
            $comment->setUser($this->getUser());
            $comment->setPublishDate(new \DateTime('now'));
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Votre commentaire a bien été ajouté.');
            return $this->redirectToRoute('app_chapter_show', ['slugPub' => $publication->getSlug(), 'user' => $publication->getUser()->getUsername(), 'idChap' => $chapter->getId(), 'slug' => $chapter->getSlug()]);
        }
        // * On ajoute un view pour le chapitre (si l'utilisateur n'est pas l'auteur de la publication)
        $this->viewChapter($chapter);
        // * On formate les notes du chapitre de l'utilisateur connecté

        $chapterContent = $this->formatChapter($chapter);

        // * La vue
        return $this->render('publication/show_chapter.html.twig', [
            'infoPub' => $publication,
            'infoChap' => $chapter,
            'previousChap' => $previous,
            'nextChap' => $next,
            'form' => $form->createView(),
            "comments" => $comments,
            "nbrShowCom" => $nbrShowCom,
            "chapterContent" => $chapterContent,
        ]);
    }
    #[Route('/recit/comment/del/{id}', name: 'app_chapter_del_comment')]
    public function delComment(Request $request, EntityManagerInterface $em, PublicationChapterCommentRepository $pccRepo, $id): response
    {
        $comment = $pccRepo->find($id);
        if ($comment and $comment->getUser() == $this->getUser()) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Votre commentaire a bien été supprimé.');
            return $this->redirectToRoute('app_chapter_show', ['slugPub' => $comment->getChapter()->getPublication()->getSlug(), 'user' => $comment->getChapter()->getPublication()->getUser()->getUsername(), 'idChap' => $comment->getChapter()->getId(), 'slug' => $comment->getChapter()->getSlug()]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
    #[Route('/recit/comment/up', name: 'app_chapter_up_comment', methods: ['POST'])]
    public function upComment(Request $request, PublicationChapterCommentRepository $pccRepo, EntityManagerInterface $em): response
    {
        $dtIdCom = $request->get("idCom");
        $dtNewCom = $request->get("newCom");
        $comment = $pccRepo->find($dtIdCom);
        if ($comment and $comment->getUser() == $this->getUser()) {
            $comment->setContent($dtNewCom);
            $em->persist($comment);
            $em->flush();
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
            'comment' => $comment->getContent()
        ], 200);
    }
    #[Route('/recit/comment/like', name: 'app_chapter_like_comment', methods: ['POST'])]
    public function likeComment(Request $request, PublicationChapterCommentRepository $pccRepo, EntityManagerInterface $em): response
    {
        $dtIdCom = $request->get("idCom");
        // * On récupère le commentaire
        $comment = $pccRepo->find($dtIdCom);
        // * Si le commentaire existe et que l'auteur du commentaire n'est pas l'auteur du like
        if ($comment and $comment->getUser() != $this->getUser()) {
            // * On vérifie que le commentaire n'a pas déjà été liké par l'utilisateur
            $like = $comment->getPublicationChapterCommentLikes()->filter(function ($like) {
                return $like->getUser() == $this->getUser();
            })->first();
            // * Si le commentaire a déjà été liké, on supprime le like
            if ($like) {
                $em->remove($like);
                $em->flush();
                return $this->json([
                    'code' => 200,
                    'message' => 'Le like a bien été supprimé.'
                ], 200);
            }
            // * Sinon, on ajoute le like
            $like = new PublicationChapterCommentLike();
            $like->setUser($this->getUser())
                ->setComment($comment)
                ->setLikeDate(new \DateTime('now'));
            $em->persist($like);
            $em->flush();
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
        ], 200);
    }
    /** Status des notes :
     * 0 = Highlight
     */
    #[Route('/recit/chapter/note', name: 'app_chapter_note', methods: ['POST'])]
    public function chapterNote(Request $request, PublicationChapterRepository $pchRepo, PublicationChapterNoteRepository $pcnRepo, EntityManagerInterface $em): response
    {
        $dtIdChapter = $request->get("idChapter");
        $dtType = $request->get("type");
        $dtStart = $request->get("start");
        $dtEnd = $request->get("end");
        $dtSuround = $request->get("surround");
        $dtContent = $request->get("content");
        // * On récupère le chapitre
        $chapter = $pchRepo->find($dtIdChapter);
        // * Si le chapitre existe et que l'utilisateur est connecté, on traite
        if ($chapter and $this->getUser()) {
            // Si le type est "highlight", on ajoute la valeur de $dtContent dans la bdd en statut 0
            if ($dtType == "highlight") {
                $note = new PublicationChapterNote();
                $note->setUser($this->getUser())
                    ->setChapter($chapter)
                    ->setType(0)
                    ->setStart($dtStart)
                    ->setEnd($dtEnd)
                    ->setSurround($dtSuround)
                    ->setSelection($dtContent)
                    ->setAddDate(new \DateTime('now'));
                $em->persist($note);
                $em->flush();
            }
        } else {
            return $this->json([
                'code' => 403,
                'message' => 'Vous n\'avez pas les droits pour modifier ce commentaire.',
            ], 403);
        }
        //
        return $this->json([
            'code' => 201,
            'message' => "L'action a bien été effectuée."
        ], 200);
    }
    public function viewChapter($chapter)
    {
        $view = new PublicationChapterView();
        if ($this->getUser()) {
            $viewRepo = $this->em->getRepository(PublicationChapterView::class);
            // On vérifie que l'utilisateur n'a pas vu le chapitre depuis plus d'une heure
            $vieww = $viewRepo->findOneBy(['user' => $this->getUser(), 'chapter' => $chapter]);
            if ($vieww) {
                $date = $vieww->getViewDate();
                $date->add(new \DateInterval('PT1H'));
                if ($date > new \DateTime('now')) {
                    return;
                } else {
                    $view->setUser($this->getUser());
                }
            } else {
                $view->setUser($this->getUser());
            }
        } else {
            $view->setUser(null);
        }
        $view->setChapter($chapter);
        $view->setViewDate(new \DateTime('now'));
        $this->em->persist($view);
        $this->em->flush();
    }
    public function formatChapter($chapter)
    {
        $note = $this->chapterNote->findBy(['chapter' => $chapter, 'type' => 0, 'user' => $this->getUser()]);
        $chapter = $chapter->getContent();
        // On récupère toutes les notes du chapitre et on fait un str_replace sur $chapter à chaque occurrence simmilaire à "selection" (de la Note)
        foreach ($note as $n) {
            // On cherche la valeur de "selection" dans le surround, on découpe surround en trois variable, avant et après la selection, et la selection
            $string = $n->getSurround();
            $search = $n->getSelection();
            $before = "";
            $after = "";
            $pos = strpos($string, $search);
            if ($pos !== false) {
                $before = substr($string, 0, $pos);
                $after = substr($string, $pos + strlen($search));
                // $chapter = $this->restore_tags($chapter);
                // $chapter = str_replace($string, $before . '<i class="fa-solid fa-highlighter"></i>' . $n->getSelection() . $after, restore_tags($chapter));
            }
        }
        return  $chapter;
    }
    #[Route('/recit/chapter/getnote', name: 'app_chapter_note', methods: ['POST'])]
    public function teeest(Request $request, PublicationChapterNoteRepository $pcnRepo, PublicationChapterRepository $pchRepo)
    {
        $dtIdChapter = $request->get("idChapter");
        // on cherche le chapitre 
        $chapter = $pchRepo->find($dtIdChapter);
        // je récupère toutes les notes en statut 0 du chapitre de l'utilisateur connecté 
        $note = $pcnRepo->findBy(['chapter' => $chapter, 'type' => 0]);

        // Je renvoie les notes en json
        return $this->json([
            'code' => 200,
            'message' => json_encode($note),
        ], 200);
    }
}
