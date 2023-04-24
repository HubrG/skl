<?php

namespace App\Controller\Publication;

use PHPePub\Core\EPub;
use PHPePub\Core\Logger;
use PHPZip\Zip\File\Zip;
use PHPePub\Helpers\URLHelper;
use PHPePub\Helpers\CalibreHelper;
use App\Entity\PublicationBookmark;
use App\Form\PublicationCommentType;
use App\Services\NotificationSystem;
use App\Services\HtmlToEpubConverter;
use PHPePub\Core\EPubChapterSplitter;
use App\Entity\PublicationChapterLike;
use App\Entity\PublicationChapterNote;
use App\Entity\PublicationChapterView;
use App\Services\PublicationPopularity;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use PHPePub\Core\Structure\OPF\MetaValue;
use PHPePub\Core\Structure\OPF\DublinCore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationCommentRepository;
use App\Repository\PublicationBookmarkRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\PublicationChapterLikeRepository;
use App\Repository\PublicationChapterNoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChapterShowController extends AbstractController
{

    private $em;
    private $chapterNote;
    private $notificationSystem;
    private $publicationPopularity;
    private $requestStack;
    private $pRepo;

    private $pchRepo;


    public function __construct(PublicationChapterRepository $pchRepo, PublicationRepository $pRepo, RequestStack $requestStack, NotificationSystem $notificationSystem, EntityManagerInterface $em, PublicationChapterNoteRepository $chapterNote, PublicationPopularity $publicationPopularity)
    {
        $this->em = $em;
        $this->chapterNote = $chapterNote;
        $this->publicationPopularity = $publicationPopularity;
        $this->notificationSystem = $notificationSystem;
        $this->requestStack = $requestStack;
        $this->pchRepo = $pchRepo;
        $this->pRepo = $pRepo;
    }

    #[Route('/recit-{slugPub}/{user}/{idChap}/{slug?}/{nbrShowCom?}', name: 'app_chapter_show')]
    public function showChapter(Request $request, PublicationCommentRepository $pcomRepo, PublicationChapterRepository $pchRepo, EntityManagerInterface $em, PublicationChapterNoteRepository $pcnRepo, PublicationRepository $pRepo, $slugPub = null, $slug = null, $idChap = null, $user = null, $nbrShowCom = null): response
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
            $comment->setPublishedAt(new \DateTimeImmutable());
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
        // * On ajoute un view pour le chapitre (si l'utilisateur n'est pas l'auteur de la publication)
        $this->viewChapter($chapter);
        // * On formate les notes du chapitre de l'utilisateur connecté

        if ($this->getUser()) {
            $chapterContent = $this->formatChapter($chapter);
            $chapterContent = $this->formatHL($chapterContent, $chapter);
        } else {
            $chapterContent = $this->formatChapter($chapter);
        }
        // * La vue
        return $this->renderForm('publication/show_chapter.html.twig', [
            'infoPub' => $publication,
            'infoChap' => $chapter,
            'previousChap' => $previous,
            'nextChap' => $next,
            'form' => $form,
            'formQuote' => $form,
            "pCom" => $comments,
            "nbrShowCom" => $nbrShowCom,
            "nbrCom" => $nbrCom,
            "chapterContent" => $chapterContent,
            "canonicalUrl" => $this->generateUrl('app_chapter_show', ["slugPub" => $slugPub, "user" => $user, "idChap" => $idChap, "slug" => $slug], true)
        ]);
    }

    /** Status des notes :
     * 0 = Highlight
     */
    #[Route('/recit/chapter/note', name: 'app_chapter_note', methods: ['POST'])]
    public function chapterNote(Request $request, PublicationChapterRepository $pchRepo, PublicationChapterNoteRepository $pcnRepo, EntityManagerInterface $em): response
    {
        // * On récupère les données
        $dtIdChapter = $request->get("idChapter");
        $dtType = $request->get("type");
        $dtP = $request->get("p");
        $dtContext = $request->get("context");
        $dtColor = $request->get("color");
        $dtSelection = $request->get("selection");
        $dtContentEl = $request->get("contentEl");
        if ($dtContext == "undefined") {
            $dtContext = null;
        }
        // * On supprimer les balises HTML en début et fin de chaîne
        $dtPContent = strip_tags($dtContentEl, "<p>");
        $dtContentEl = preg_replace('/^(?:<[^>]+>)+|(?:<\/[^>]+>)+$/', '', $dtContentEl);
        // * On supprime les retours à la ligne en début et fin de chaîne sur $Content
        $lines = explode("\n", $dtSelection);
        $dtSelection = reset($lines);
        // * On récupère le chapitre
        $chapter = $pchRepo->find($dtIdChapter);
        // * On traire les paragraphes multiples

        // *
        // * Si le chapitre existe et que l'utilisateur est connecté, on traite
        if ($chapter and $this->getUser()) {
            // Si le type est "highlight", on ajoute la valeur de $dtContent dans la bdd en statut 0
            if ($dtType == "highlight") {
                // * On envoie l'highlight dans la BDD
                $note = new PublicationChapterNote();
                $note->setUser($this->getUser())
                    ->setChapter($chapter)
                    ->setType(0)
                    ->setColor($dtColor)
                    ->setP($dtP)
                    ->setContext($dtContext)
                    ->setSelection($dtSelection)
                    ->setSelectionEl($dtContentEl)
                    ->setPContent($dtPContent)
                    ->setAddDate(new \DateTime('now'));
                $em->persist($note);
                $em->flush();

                // * On récupère l'ID de l'highlight
                $idNote = $note->getId();
                $selectedTextEl = $note->getSelectionEl();
                $selectedText = $note->getSelection();
                $context = $note->getContext();
                $p = $note->getP();
                // * On renvoie l'ID de l'highlight
                return $this->json([
                    'code' => 201,
                    'message' => 'L\'highlight a bien été ajouté.',
                    'idNote' => $idNote,
                    'selectionEl' => $selectedTextEl,
                    'selection' => $selectedText,
                    'contextSel' => $context,
                    'p' => $p,
                ], 200);
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
    public function formatChapter($chapter)
    {
        $id = 0;
        $content = $chapter->getContent();

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

    #[Route('/recit/chapter/getnote', name: 'app_chapter_get_note', methods: ['POST'])]
    public function Axios_getNotes(Request $request, PublicationChapterNoteRepository $pcnRepo, PublicationChapterRepository $pchRepo)
    {
        $dtIdChapter = $request->get("idChapter");
        // on cherche le chapitre 
        $chapter = $pchRepo->find($dtIdChapter);
        // je récupère toutes les notes en statut 0 du chapitre de l'utilisateur connecté 
        $note = $pcnRepo->findBy(['chapter' => $chapter, 'type' => 0]);
        // Je récupère toutes les données de $note et je les mets dans un tableau
        $note = array_map(function ($note) {
            return [
                'id' => $note->getId(),
                'contextSel' => $note->getContext(),
                'selection' => $note->getSelection(),
                'selectionEl' => $note->getSelectionEl(),
                'color' => $note->getColor(),
                'p' => $note->getP(),
            ];
        }, $note);
        // Je renvoie les notes en json
        return $this->json([
            'code' => 200,
            'message' => $note,
        ], 200);
    }
    private function getNoteToDelete(Request $request, PublicationChapterNoteRepository $pcnRepo)
    {
        $dtIdNote = $request->get("idNote");
        return $pcnRepo->find($dtIdNote);
    }

    private function checkUserHasRights(PublicationChapterNote $note)
    {
        return $note->getUser() == $this->getUser();
    }

    #[Route('/recit/chapter/delnote', name: 'app_chapter_delete_note', methods: ['POST'])]
    public function Axios_DeleteNotes(Request $request, PublicationChapterRepository $pchRepo, PublicationChapterNoteRepository $pcnRepo, EntityManagerInterface $em): response
    {
        $note = $this->getNoteToDelete($request, $pcnRepo);
        if ($this->checkUserHasRights($note)) {
            $em->remove($note);
            $em->flush();
            return $this->json([
                'code' => 200,
                'message' => 'La note a bien été supprimée.',
            ], 200);
        } else {
            return $this->json([
                'code' => 403,
                'message' => 'Vous n\'avez pas les droits pour supprimer cette note.',
            ], 403);
        }
    }

    public function formatHL($chapter, $chapterTab)
    {
        $notes = $this->chapterNote->findBy(['chapter' => $chapterTab, 'type' => 0, 'user' => $this->getUser()]);
        if (!$notes) {
            return $chapter;
        }
        $chapterText = $chapter;
        foreach ($notes as $note) {
            $contextSel = $note->getContext();
            $selection = $note->getSelection();
            $color = $note->getColor();
            $selectionEl = $note->getSelectionEl();
            $idNote = $note->getId();
            $tests = $contextSel . $selection;
            $regex = '/<p id="paragraphe-' . $note->getP() . '"(.*?)>(.*?)<\/p>/';
            preg_match($regex, $chapterText, $match);
            // On reformate le $selectionEl pour ne conserver que ce qui précède la première balise fermante </p> de la chaîne
            if (strpos($selectionEl, "</p>") !== false) {
                if (strpos($selectionEl, "</p>")) {
                    $selectionEl2 = substr($selectionEl, 0, strpos($selectionEl, "</p>"));
                    preg_match_all('/<[^>]*>|[^<]+/', $selectionEl2, $matches);
                    $n = 0;
                    foreach ($matches[0] as $key => $value) {
                        if (strpos($value, "<") === false) {
                            $matches[0][$key] = "<span id='hl-" . $idNote . "' class='hlId-" . $idNote . " highlighted hl-" . $color . " hlMultiple'>" . $value . "</span>";
                        }
                        $n++;
                    }
                    // on immlode
                    $selectionEl3 = implode("", $matches[0]);

                    $chapterText = str_replace($selectionEl2, "<span id='hl-" . $idNote . "' class='hlId-" . $idNote . " highlighted hl-" . $color . "'>" . $selectionEl3 . "</span>", $chapterText);
                } else {
                    $chapterText = str_replace($selectionEl, "<span id='hl-" . $idNote . "' class='hlId-" . $idNote . " highlighted hl-" . $color . "'>" . $selectionEl . "</span>", $chapterText);
                }
                $chapterText = str_replace(
                    $selectionEl,
                    `<span id='hl-" . $idNote . "' class='highlighted hlId-" . $idNote . " hl-" . $color . "'>" . $selectionEl . "</span>`,
                    $chapterText
                );
            } else {
                if (strpos($match[0], $tests)) {
                    $test = str_replace($tests, $contextSel . "<span id='hl-" . $idNote . "' class='hlId-" . $idNote . " highlighted hl-" . $color . "'>" . $selection . "</span>", $match[0]);
                    $chapterText = str_replace($match[0], $test, $chapterText);
                } elseif (strpos($match[0], $selectionEl)) {
                    $test = str_replace($selectionEl, "<span id='hl-" . $idNote . "' class='highlighted hlId-" . $idNote . " hl-" . $color . "'>" . $selection . "</span>", $match[0]);
                    $chapterText = str_replace($match[0], $test, $chapterText);
                } else {
                    $test = str_replace($selection, "<span id='hl-" . $idNote . "' class='highlighted hlId-" . $idNote . " hl-" . $color . "'>" . $selection . "</span>", $match[0]);
                    $chapterText = str_replace($match[0], $test, $chapterText);
                }
            }
        }
        return $chapterText;
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
        $like->setCreatedAt(new \DateTimeImmutable('now'));
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
        $bm->setCreatedAt(new \DateTimeImmutable('now'));
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

        // setting timezone for time functions used for logging to work properly
        date_default_timezone_set('Europe/Paris');

        $fileDir = './PHPePub';

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
            $content = $content_start . "<h1>" . $chapter->getTitle() . "</h1>\n" . $cleanContent . $bookEnd;
            $book->addChapter($chapter->getTitle(), $chapter->getSlug() . '.html', $content, true, EPub::EXTERNAL_REF_ADD);
        }

        $book->finalize(); // Finalize the book, and build the archive.

        // Send the book to the client. ".epub" will be appended if missing.
        $zipData = $book->sendBook($publication->getTitle() . " - " . $publication->getUser()->getNickname());
        return $zipData;
    }
}
