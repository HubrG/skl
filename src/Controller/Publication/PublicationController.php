<?php

namespace App\Controller\Publication;

use DateTimeImmutable;
use DirectoryIterator;
use Cloudinary\Cloudinary;
use App\Entity\Publication;
use App\Form\PublicationType;
use App\Services\ImageService;
use App\Entity\PublicationAccess;
use App\Entity\PublicationKeyword;
use App\Repository\UserRepository;
use App\Form\PublicationAccessType;
use App\Services\NotificationSystem;
use App\Repository\UserFollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PublicationAccessRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationKeywordRepository;
use App\Repository\PublicationCategoryRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PublicationController extends AbstractController
{

    public function __construct(
        private Cloudinary $cloudinary,
        private ImageService $uploadImage,
        private ChapterController $chapterController,
        private NotificationSystem $notificationSystem
    ) {
    }

    #[Route('/story/add', name: 'app_publication_add')]
    public function Draft(Request $request, PublicationRepository $pubRepo, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        // * If user is connected
        if ($this->getUser()) {
            // * We get our last draft, if it exists
            $brouillon = $pubRepo->findOneBy(["user" => $this->getUser(), "status" => 0]);
            if (!$brouillon) {
                $publication = new Publication();
                $publication->setUser($this->getUser());
                $publication->setStatus(0);
                $publication->setAccess(0);
                $publication->setHideSearch(0);
                $publication->setAllowRevision(1);
                $publication->setShowOldVersions(1);
                $publication->setType(0);
                $publication->setMature(0);
                $em->persist($publication);
                $em->flush();
                $brouillon = $pubRepo->findOneBy(["user" => $this->getUser(), "status" => 0]);
            }
            $form = $this->createForm(PublicationType::class, $brouillon);
        } else {
            return $this->redirectToRoute("app_home");
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // * we modify the status of the post => (1) it is no longer a draft
            $status = $pubRepo->findOneBy(["user" => $this->getUser(), "status" => 0]);
            $status->setStatus(1)
                ->setUpdated(new \DateTime('now'))
                ->setCreated(new \DateTime('now'))
                ->setPop(0);
            // * if the title is empty...
            if ($form->get("title")->getViewData() === "") {
                $status->setTitle("Récit sans titre")
                    ->setSlug("recit-sans-titre");
            } else {
                $title = trim(ucfirst($form->get("title")->getViewData()));
                $status->setTitle($title)
                    ->setSlug($slugger->slug(strtolower($title)));
            }
            // * We format the summary
            $status->setSummary(trim(ucfirst($form->get("summary")->getViewData())));
            $em->persist($form->getData());
            $em->persist($status);
            $em->flush();
            //
            return $this->redirectToRoute("app_publication_edit", ["id" => $brouillon->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('publication/add_publication.html.twig', [
            'form' => $form,
            'pub' => $brouillon
        ]);
    }
    #[Route('/story/edit/{id}', name: 'app_publication_edit')]
    public function EditPublication(EntityManagerInterface $em, Request $request, PublicationAccessRepository $pacRepo, UserRepository $uRepo, PublicationRepository $pubRepo, PublicationChapterRepository $pchRepo, $id = null): Response
    {

        $infoPublication = $pubRepo->findOneBy(["id" => $id]);
        //
        if ($this->getUser() === $infoPublication->getUser() or $this->isGranted("ROLE_ADMIN")) {
            // On recherceh tous les chapitres de la publication avec le "statut" => 0
            $chaptersWithStatus0 = $pchRepo->findChaptersByPublicationAndStatus($infoPublication, 0);
            // On vérifie si la durée est supérieure à 1 minute
            foreach ($chaptersWithStatus0 as $trash) {
                $trashAt = $trash->getTrashAt();
                $now = new DateTimeImmutable('now');
                $interval = $now->diff($trashAt);
                $hours = (int)$interval->format('%h');
                $days = (int)$interval->format('%a');

                if ($days >= 2 || ($days === 1 && $hours >= 0)) {
                    $this->chapterController->funcDeleteChapter($trash);
                }
            }

            //
            $chaptersWithStatus0 = $pchRepo->findChaptersByPublicationAndStatus($infoPublication, 0);
            $chaptersWithStatus2 = $pchRepo->findChaptersByPublicationAndStatus($infoPublication->getId(), 2);
            $chaptersWithStatus1 = $pchRepo->findChaptersByPublicationAndStatus($infoPublication->getId(), 1);
            // On recherche tous les chapitres de la publication avec le "statut" => 2
            if ($infoPublication) {
                $formPub = $this->createForm(PublicationType::class, $infoPublication);
                // * USER ACCESS - AJOUT D'UN UTILISATEUR
                // ! S'il y a la variable userAccessId dans l'url, on recherche les utilisateurs ayant accès à la publication
                $userAccessVar = $request->query->get('userAccessId');
                if ($userAccessVar) {
                    $userAccess = $uRepo->find($userAccessVar);
                    // On vérifie que cet utilisateur n'a pas déjà accès à la publication
                    $userAccessExist = $pacRepo->findOneBy(["user" => $userAccess, "publication" => $infoPublication]);
                    if (!$userAccessExist) {
                        // On vérifie que l'utilisateur n'est pas l'auteur de la publication
                        if ($userAccess !== $infoPublication->getUser()) {
                            // On l'ajoute à l'entité PublicationAccess
                            $publicationAccess = new PublicationAccess();
                            $publicationAccess->setUser($userAccess)
                                ->setPublication($infoPublication);
                            $em->persist($publicationAccess);
                            $em->flush();
                        } else {
                            $userAccess = null;
                        }
                    } else {
                        $userAccess = null;
                    }
                } else {
                    $userAccess = null;
                }
                // * USER ACCESS - SUPPRESSION D'UN UTILISATEUR
                // ! S'il y a la variable deleteUserAccessId dans l'url, on on recherche l'utilisateur pour le supprimer
                $deleteUserAccessVar = $request->query->get('deleteUserAccessId');
                if ($deleteUserAccessVar) {
                    $userAccess = $uRepo->find($deleteUserAccessVar);
                    // On vérifie que cet utilisateur a bien accès à la publication
                    $userAccessExist = $pacRepo->findOneBy(["user" => $userAccess, "publication" => $infoPublication]);
                    if ($userAccessExist) {
                        // On vérifie que l'utilisateur n'est pas l'auteur de la publication
                        if ($userAccess !== $infoPublication->getUser()) {
                            // On le supprime de l'entité PublicationAccess
                            $em->remove($userAccessExist);
                            $em->flush();
                        } else {
                            $userAccess = null;
                        }
                    } else {
                        $userAccess = null;
                    }
                } else {
                    $userAccess = null;
                }
                // * USER ACCESS - AFFICHAGE
                $userAccess = $pacRepo->findBy(["publication" => $infoPublication]);
                // * RETURN
                return $this->render('publication/edit_publication.html.twig', [
                    'infoPub' => $infoPublication,
                    'formPub' => $formPub,
                    'chaptersWithStatus2' => $chaptersWithStatus2,
                    'chaptersWithStatus1' => $chaptersWithStatus1,
                    'chaptersWithStatus0' => $chaptersWithStatus0,
                    'userAccess' => $userAccess
                ]);
            } else {
                return $this->redirectToRoute("app_home");
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }
    // ! //////////////////////////////////////////////////////////////////////////////////////////////////////
    // ! //////////////////////////////////////////////////////////////////////////////////////////////////////
    // ! //////////////////////////////////////////////////////////////////////////////////////////////////////
    // * ROUTES PERMETTANT LA GESTION DE DONÉES EN BACKGROUND (ADD KEYWORD / DEL KEYWORD / AUTOSAVE)
    // ! //////////////////////////////////////////////////////////////////////////////////////////////////////
    // ! //////////////////////////////////////////////////////////////////////////////////////////////////////
    // ! //////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Keywords management is done in Ajax, this road allows you to add a keyword to a post
     * 
     * 1) This function adds the link between the post and the keyword via manytomany
     * 2) If Keyword does not already exist in BDD, we create it, otherwise, we do nothing.
     * 3) The Keyword "Count" is incremented only used if the Publiciton Parente is published. Indeed, the keywords which only have posts that drafts are not counted to avoid the keywords ghostly
     */
    #[Route('story/add_key/{pub<\d+>?0}/{value}', name: 'app_publication_add_keyword', methods: 'POST',)]
    public function Axios_AddKey(PublicationKeywordRepository $keyRepo, PublicationRepository $pubRepo, EntityManagerInterface $em, $pub = null, $value = null): Response
    {
        // * If value is set and the user is logged in...
        if ($value && $this->getUser()) {
            $value = trim(ucfirst($value));
            $keyExists = $keyRepo->findOneBy(["keyword" => $value]);
            $publication = $pubRepo->find($pub);
            // * If the post exists
            if ($publication) {
                // * If the connected user is the author of the post, we continue...
                if ($this->getUser() === $publication->getUser() or $this->isGranted("ROLE_ADMIN")) {
                    // Si le mot clé existe déjà...
                    if ($keyExists) {
                        // ... alors on ne le recrée pas et on lui ajoute 1 occurrence (uniquement si l'article est publié)
                        if ($publication->getStatus() === 2) {
                            $countKey = $keyExists->getCount() + 1;
                            $key = $keyExists->setCount($countKey)
                                // on ajoute le mot au ManyToMany de l'article correspondant
                                ->addPublication($publication);
                        }
                        // ... Sinon, on ne le recrée pas, mais on ne lui ajoute pas d'occurrence, on ajoute seulement le mot au ManyToMany
                        else {
                            $key = $keyExists->addPublication($publication);
                        }
                    }
                    // sinon, on crée le nouveau mot et on l'ajoute au ManyToMany de l'article et on setcount 1 (uniquement si l'article est publié).....
                    else {
                        $keykey = new PublicationKeyword();
                        $key = $keykey->setKeyword($value)
                            ->addPublication($publication);
                        if ($publication->getStatus() === 2) {
                            $key->setCount(1);
                        } else {
                            $key->setCount(0);
                        }
                    }
                    $em->persist($key);
                    $em->flush();
                    return $this->json(["code" => "200", "value" => $value]);
                } else {
                    return $this->redirectToRoute("app_home");
                }
            } else {
                return $this->redirectToRoute("app_home");
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }
    /**
     * Keywords management is done in Ajax, this road allows you to add a keyword to a post
     * 
     * 1) This function remove the link between the post and the keyword via manytomany
     * 2) The Keyword "Count" is decremented only if the Publiciton Parente is published.
     */
    #[Route('story/{mode}/del_key/{pub<\d+>?0}/{value}', name: 'app_publication_del_keyword')]
    public function Axios_DelKey(PublicationKeywordRepository $keyRepo, PublicationRepository $pubRepo, EntityManagerInterface $em, $pub = null, $value = null, $mode = null): Response
    {
        // * Si value est set et que l'utilisateur est connecté...
        if ($value && $this->getUser()) {
            $delKey = $keyRepo->findOneBy(["keyword" => $value]);
            $publication = $pubRepo->find($pub);
            // * Si l'user authentitifé est bien l'auteur du post...
            if ($publication->getUser() === $this->getUser() or $this->isGranted("ROLE_ADMIN")) {
                // * Si le mot existe alors...
                if ($delKey) {
                    // * On verifie que le post existe et que ce keyword est bien lié au post
                    if ($delKey->getPublication()) {
                        // * On décrémente le keyword dissocié (uniquement si l'article est publié)
                        if ($publication->getStatus() === 2) {
                            $countKey = $delKey->getCount() - 1;
                            $delKey->setCount($countKey);
                        }
                        // * et son supprime le manytomany du post et du keyword
                        $delKey->removePublication($publication);
                        $em->persist($delKey);
                        $em->flush();
                        // * On redirige vers la page d'édition du post
                        if ($mode === "edit") {
                            return $this->redirectToRoute("app_publication_edit", ["id" => $pub], Response::HTTP_SEE_OTHER);
                        } else {
                            return $this->redirectToRoute("app_publication_add", [], Response::HTTP_SEE_OTHER);
                        }
                    } else {
                        return $this->redirectToRoute("app_home");
                    }
                } else {
                    return $this->redirectToRoute("app_home");
                }
            } else {
                return $this->redirectToRoute("app_home");
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }
    /**
     * This function makes it possible to manage the publication of a post via Ajax on the site
     *
     * 1) She first manages the change of post status, but also the management of keywords related to the post.
     * 2) If the post is published, then we increment the count of the keywords related to the post
     * 3) If the post is depubliated, then we decrease the count of the keywords linked to the post
     */
    #[Route('/story/publish', name: 'app_publication_publish', methods: 'POST')]
    public function Axios_Publish(Request $request, UserFollowRepository $ufRepo, PublicationRepository $pubRepo, EntityManagerInterface $em): Response
    {
        $dataPub = $request->get("pub");
        $dataPublish = json_decode($request->get("publish"));
        //
        $publication = $pubRepo->find($dataPub);
        //
        if ($this->getUser() == $publication->getUser() or $this->isGranted("ROLE_ADMIN")) {
            if ($dataPublish) {
                $return = 200;
                $publication->setStatus(2);
                if ($publication->getPublishedDate() === null) {
                    $publication->setPublishedDate(new \DateTime('now'));
                }
                $publication->setLastPublishedAt(new DateTimeImmutable('now'));
                $keywords = $publication->getPublicationKeywords();
                foreach ($keywords as $key) {
                    $countKey = $key->getCount() + 1;
                    $key->setCount($countKey);
                    $em->persist($key);
                    $em->flush();
                }
                // * Envoi d'une notification aux abonnés de la publication
                // Si la publication est publiée, on envoie une notification à tous les abonnés de la publication
                if ($publication->getStatus() == 2) {
                    $followers = $ufRepo->findBy(["toUser" => $this->getUser()]);
                    foreach ($followers as $follower) {
                        $this->notificationSystem->addNotification(19, $follower->getFromUser(), $this->getUser(), $publication);
                    }
                }
            } else {
                $return = 201;
                $publication->setStatus(1);
                $keywords = $publication->getPublicationKeywords();
                foreach ($keywords as $key) {
                    $countKey = $key->getCount() - 1;
                    $key->setCount($countKey);
                    $em->persist($key);
                    $em->flush();
                }
            }
            $em->persist($publication);
            $em->flush();
            return $this->json([
                "code" => $return
            ]);
        } else {
            return $this->json([
                "code" => "500", "value" => null
            ]);
        }
    }
    /**
     * Cette fonction permet la suppression complète d'une publication via Ajax
     * 
     * 1) Elle supprime toutes les traces de la publication dans la base de données avec tout ce qui lui est lié (chapitres, les commentaires et fichiers associés...).
     * 2) Elle gère également l'incrémentation/décrémentation des mots clés liés à la publication (uniquement si l'articel est publié au moment de sa suppression) 
     */
    #[Route('/story/delete/{id}', name: 'app_publication_delete')]
    public function DeletePublication(PublicationRepository $pubRepo, EntityManagerInterface $em, $id = null): Response
    {

        $publication = $pubRepo->find($id);
        // * Si l'utilisateur est bien l'auteur de la publication
        if ($this->getUser() === $publication->getUser() or $this->isGranted("ROLE_ADMIN")) {


            $keyw = $publication->getPublicationKeywords();
            // ! Gestion du keyword
            // * On décrémente le count des mots clés liés à la publication, et si le count tombe à zéro, on le supprime purement et simplement (uniquement si l'articel est publié au moment de sa suppression)
            if ($publication->getStatus() === 2) {
                foreach ($keyw as $key) {
                    $countKey = $key->getCount() - 1;
                    $setCountKey = $key->setCount($countKey);
                    $em->persist($setCountKey);
                }
            }
            // ! Suppression du dossier $id avec tous les fichiers
            if ($publication->getCover()) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/images/uploads/story/' . $id;
                if (\file_exists($destination)) {
                    foreach (new DirectoryIterator($destination) as $item) :
                        if ($item->isFile()) {
                            \unlink($item->getPathname());
                        }
                    endforeach;
                    \rmdir($destination);
                }
            }
            if ($publication->getCover()) {
                $this->uploadImage->deleteImage($destination . "/" . $publication->getCover(), $publication->getCover(), $publication->getId(), "story");
                $this->cloudinary->adminApi()->deleteFolder("story/" . $id);
            }
            $em->remove($publication);
            $em->flush();
            $this->addFlash("success", "Le récit a bien été supprimé.");
        } else {
            return $this->redirectToRoute("app_home");
        }
        return $this->redirectToRoute("app_user_show_publications");
    }
    #[Route('/story/autosave', name: 'app_publication_autosave', methods: "POST")]
    public function Axios_AutoSave(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PublicationCategoryRepository $catRepo, PublicationRepository $pRepo): response
    {
        $idPub = $request->get("idPub");
        //
        $dtTitle = $request->get("title");
        $dtSummary = $request->get("summary");
        $dtFinished = $request->get("finished") == "true" ? true : false;
        $dtAccess = $request->get("access") == "true" ? true : false;
        $dtHideSearch = $request->get("hideSearch") == "true" ? true : false;
        $dtShowOldVersions = $request->get("showOldVersions") == "true" ? true : false;
        $dtAllowRevision = $request->get("allowRevision") == "true" ? true : false;
        //
        $dtSale = $request->get("sale") == "true" ? 1 : 0;
        $dtSaleWeb = $request->get("sale_web");
        $dtSalePaper = $request->get("sale_paper");
        $dtSaleWeb = ($dtSaleWeb === "undefined") ? null : $dtSaleWeb;
        $dtSalePaper = ($dtSalePaper === "undefined") ? null : $dtSalePaper;
        //
        $dtCategory = $request->get("category");
        $dtMature = $request->get("mature");
        if ($dtMature == "true") {
            $dtMature = 1;
        } else {
            $dtMature = 0;
        }
        $dtCover = $request->files->get("cover");
        //
        $pub = $pRepo->find($idPub);
        $category = $catRepo->find($dtCategory);
        //
        $urlCloudinary = "";
        if ($this->getUser() == $pub->getUser() or $this->isGranted("ROLE_ADMIN")) {
            //
            $publication = $pub->setTitle($dtTitle)->setSlug($slugger->slug(strtolower($dtTitle)))
                ->setSummary($dtSummary)
                ->setCategory($category)
                ->setMature($dtMature)
                ->setFinished($dtFinished)
                ->setAccess($dtAccess)
                ->setHideSearch($dtHideSearch)
                ->setShowOldVersions($dtShowOldVersions)
                ->setAllowRevision($dtAllowRevision)
                ->setSale($dtSale)
                ->setSaleWeb($dtSaleWeb)
                ->setSalePaper($dtSalePaper)
                ->setUpdated(new \DateTime('now'));

            // * Traitement de l'image
            if ($dtCover) {
                return $this->uploadImage->UploadImage($dtCover, "story", $pub->getId(), 529, 793);
            }
            $em->persist($publication);
            $em->flush();
        } else {
            return $this->json([
                "code" => 404
            ]);
        }
        return $this->json([
            "code" => 200,
            "mature" => $dtMature // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
        ]);
    }
    #[Route('/publication/chart', name: 'app_publication_getchart', methods: "POST")]
    public function Axios_GetChart(Request $request, PublicationRepository $pRepo): response
    {
        $idPub = $request->get("idPub");
        $pub = $pRepo->find($idPub);
        $chapters = $pub->getPublicationChapters();
        $monthViews = [];
        $monthBookmarks = [];
        $monthLikes = [];
        $monthViews = array();
        $monthPop = array();
        for ($i = 1; $i <= 12; $i++) {
            $monthViews[date('M', mktime(0, 0, 0, $i, 1))] = 0;
            $monthBookmarks[date('M', mktime(0, 0, 0, $i, 1))] = 0;
            $monthLikes[date('M', mktime(0, 0, 0, $i, 1))] = 0;
            $monthComments[date('M', mktime(0, 0, 0, $i, 1))] = 0;
        }
        foreach ($chapters as $chapter) {
            // On cherche les vues de chaque chapitre
            $chapterView = $chapter->getPublicationChapterViews();
            foreach ($chapterView as $view) {
                $date = $view->getViewDate()->format("Y-m-d");
                $month = date('M', strtotime($date));
                // Stocker les vues par mois
                $monthViews[$month] += 1;
            }
            // On cherche les bookmarks de chaque chapitre
            $chapterBookmark = $chapter->getPublicationBookmarks();
            foreach ($chapterBookmark as $bookmark) {
                // S'il n'y a pas de date de création de bookrmak, on passe au suivant
                if (!$bookmark->getCreatedAt()) {
                    continue;
                }
                $date = $bookmark->getCreatedAt()->format("Y-m-d");
                $week = date('M', strtotime($date));
                if (!isset($monthBookmarks[$week])) {
                    $monthBookmarks[$week] = 0;
                }
                $monthBookmarks[$week] += 1;
            }


            // On cherche les likes de chaque chapitre
            $chapterLike = $chapter->getPublicationChapterLikes();
            foreach ($chapterLike as $like) {
                // S'il n'y a pas de date de création de bookrmak, on passe au suivant
                if (!$like->getCreatedAt()) {
                    continue;
                }
                $date = $like->getCreatedAt()->format("Y-m-d");
                $week = date('M', strtotime($date));
                if (!isset($monthLikes[$week])) {
                    $monthLikes[$week] = 0;
                }
                $monthLikes[$week] += 1;
            }
            // On cherche les commentaires de chaque publication
            $comment = $pub->getPublicationComments();
            foreach ($comment as $c) {
                // S'il n'y a pas de date de création de bookrmak, on passe au suivant
                if (!$c->getPublishedAt()) {
                    continue;
                }
                $date = $c->getPublishedAt()->format("Y-m-d");
                $week = date('M', strtotime($date));
                if (!isset($monthComments[$week])) {
                    $monthComments[$week] = 0;
                }
                $monthComments[$week] += 1;
            }
            // On cherche la popularité 
            $pop = $pub->getPublicationPopularities();
            foreach ($pop as $c) {
                // S'il n'y a pas de date de création de bookrmak, on passe au suivant
                if (!$c->getPopularity()) {
                    continue;
                }
                $date = $c->getCreatedAt()->format("Y-m-d");
                $week = date('d-m', strtotime($date));
                if (!isset($monthPop[$week])) {
                    $monthPop[$week] = round($c->getPopularity(), 3);
                }
            }
        }
        return $this->json([
            "code" => 200,
            "views" => json_encode($monthViews),
            "bookmarks" => json_encode($monthBookmarks),
            "likes" => json_encode($monthLikes),
            "comments" => json_encode($monthComments),
            "popularity" => json_encode($monthPop)
        ]);
    }
    #[Route('/story/fastedititle', name: 'app_publication_fast_edit_title', methods: "POST")]
    public function Axios_FastEditTitle(Request $request, PublicationChapterRepository $pchRepo, EntityManagerInterface $em, SluggerInterface $slugger, PublicationCategoryRepository $catRepo, PublicationRepository $pRepo): response
    {
        $id = $request->get("id");
        $dtTitle = $request->get("title");
        //
        $chap = $pchRepo->find($id);

        $pub = $chap->getPublication();
        //
        if ($this->getUser() == $pub->getUser() or  $this->isGranted("ROLE_ADMIN")) {
            $chap->setTitle($dtTitle);
            $chap->setSlug($slugger->slug(strtolower($dtTitle)));
            $em->persist($chap);
            $em->flush();
        } else {
            return $this->json([
                "code" => 404,
                "title" => $pub
            ]);
        }
        return $this->json([
            "code" => 200,
            "title" => $dtTitle
        ], 200);
    }
    #[Route('/story/deletecover', name: 'app_publication_delete_cover', methods: "POST")]
    public function Axios_DeleteCover(Request $request, PublicationRepository $pRepo, EntityManagerInterface $em): response
    {
        $id = $request->get("id");
        $pub = $pRepo->find($id);
        if ($this->getUser() == $pub->getUser() or  $this->isGranted("ROLE_ADMIN")) {
            // ! Suppression du dossier $id avec tous les fichiers
            if ($pub->getCover()) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/images/uploads/story/' . $id;
                if (\file_exists($destination)) {
                    foreach (new DirectoryIterator($destination) as $item) :
                        if ($item->isFile()) {
                            \unlink($item->getPathname());
                        }
                    endforeach;
                    \rmdir($destination);
                }
            }
            if ($pub->getCover()) {
                $this->uploadImage->deleteImage($destination . "/" . $pub->getCover(), $pub->getCover(), $pub->getId(), "story");
                $this->cloudinary->adminApi()->deleteFolder("story/" . $id);
            }
            $pub->setCover(null);
            $em->persist($pub);
            $em->flush();
        } else {
            return $this->json([
                "code" => 404
            ]);
        }
        return $this->json([
            "code" => 200
        ]);
    }
}
