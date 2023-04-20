<?php

namespace App\Controller\Publication;

use Exception;
use Cloudinary\Cloudinary;
use App\Services\ImageService;
use App\Entity\PublicationChapter;
use App\Repository\UserRepository;
use App\Services\NotificationSystem;
use App\Repository\PicturesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\NotificationRepository;
use App\Entity\PublicationChapterVersioning;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PublicationFollowRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\PublicationChapterVersioningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ChapterController extends AbstractController
{

    private $cloudinary;
    private $notificationSystem;
    private $uploadImage;


    public function __construct(Cloudinary $cloudinary, ImageService $uploadImage, NotificationSystem $notificationSystem)
    {
        $this->notificationSystem = $notificationSystem;
        $this->uploadImage = $uploadImage;
        $this->cloudinary = $cloudinary;
    }

    #[Route('/story/edit/{idPub}/chapter/{idChap?}', name: 'app_publication_edit_chapter')]
    public function EditChapter(PublicationRepository $pubRepo, PublicationChapterRepository $pcRepo, EntityManagerInterface $em, $idPub = null, $idChap = null): response
    {
        // * Si l'utilisateur est connecté, que la publication existe
        if ($this->getUser() && $pubRepo->find($idPub)) {
            // on récupère les informations de la publication
            $infoPublication = $pubRepo->find($idPub);
            // * Si l'utilisateur est bien l'auteur de la publication, on continue, sinon on est redirigé vers la page d'accueil
            if ($this->getUser() == $infoPublication->getUser()) {
                // * s'il n'y a pas d'ID de chapitre dans l'URL, on vérifie si un chapitre brouillon existe pour cet utilisateur
                if (!$idChap) {
                    // * on vérifie si un chapitre brouillon existe pour cet utilisateur
                    $pcExists = $pcRepo->findOneBy(['publication' => $idPub, "status" => -1]);
                    // * si le chapitre n'existe pas en brouillon, on le crée
                    if (!$pcExists) {
                        // ! on l'ajoute aux chapitres
                        // ! On récupère le nombre de chapitres liés à cette publication afin de donner un nouveau titre (Feuille X)
                        $nbrChap = $pcRepo->findBy(['publication' => $idPub]);
                        $nbrChap = count($nbrChap) + 1;
                        $chapTitleExist = $pcRepo->findBy(['publication' => $idPub, 'title' => 'Feuille n°' . $nbrChap]);
                        $countSameTitle = count($chapTitleExist);
                        if ($chapTitleExist) {
                            $chapAdd = " (" . $countSameTitle . ")";
                        } else {
                            $chapAdd = "";
                        }
                        $pc = new PublicationChapter;
                        $publicationChapter = $pc->setCreated(new \DateTime('now'))
                            ->setStatus(-1) // 0 = brouillon / 1 = en cours de rédaction
                            ->setPublication($infoPublication)
                            ->setTitle("Feuille n°" . $nbrChap . $chapAdd)
                            ->setSlug("feuille-n0" . $nbrChap . $chapAdd)
                            ->setPop(0)
                            ->setOrderDisplay($nbrChap);
                        $em->persist($publicationChapter);
                        // ! on l'ajoute au versioning
                        $pcv = new PublicationChapterVersioning;
                        $publicationChapterVersioning = $pcv->setCreated(new \DateTime('now'))
                            ->setTitle("Feuille n°" . $nbrChap)
                            ->setChapter($publicationChapter);
                        $em->persist($publicationChapterVersioning);
                        $em->flush();
                    }
                    // * On récupère le chapitre qui vient d'être créé
                    $infoChapitre = $pcRepo->findOneBy(['publication' => $idPub, "status" => -1]);
                    // * Et on redirige
                    return $this->redirectToRoute('app_publication_edit_chapter', ['idPub' => $idPub, 'idChap' => $infoChapitre->getId()]);
                }
                // * Sinon, si l'ID du chapitre existe dans l'URL, on le récupère
                else {
                    $infoChapitre = $pcRepo->find($idChap);
                    // * On vérifie que le chapitre existe bel et bien dans la BDD
                    if ($infoChapitre) {
                        // * On vérifie que le chapitre de l'URL est bien lié à la publication de l'URL
                        if ($infoChapitre->getPublication() == $infoPublication) {
                            // * Si le chapitre est en statut -1 (pré-brouillon), on le passe en statut 1 (en cours de rédaction)
                            if ($infoChapitre->getStatus() === -1) {
                                $infoChapitre->setStatus(1);
                            }
                            //
                            $em->persist($infoChapitre);
                            $em->flush();
                        } else {
                            return $this->redirectToRoute('app_home');
                        }
                    } else {
                        return $this->redirectToRoute('app_home');
                    }
                }
                // *
                return $this->render('publication/edit_chapter.html.twig', [
                    'infoPub' => $infoPublication,
                    'infoChap' => $infoChapitre,
                    'chaps' => $pcRepo->findBy(['publication' => $idPub], ['order_display' => 'ASC']),
                ]);
            } else {
                return $this->redirectToRoute('app_home');
            }
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
    #[Route('/story/edit/{idPub}/chapter/{idChap}/delete', name: 'app_publication_del_chapter')]
    public function DelChapter(PublicationRepository $pubRepo, PicturesRepository $picRepo, PublicationChapterRepository $pcRepo, EntityManagerInterface $em, $idPub = null, $idChap = null): response
    {
        // * Si l'utilisateur est connecté, que la publication existe
        if ($this->getUser() && $pubRepo->find($idPub)) {
            // on récupère les informations de la publication
            $infoPublication = $pubRepo->find($idPub);
            // * Si l'utilisateur est bien l'auteur de la publication, on continue, sinon on est redirigé vers la page d'accueil
            if ($this->getUser() == $infoPublication->getUser()) {
                // * On récupère le chapitre qui vient d'être crée
                $infoChapitre = $pcRepo->find($idChap);
                // * On vérifie que le chapitre existe bel et bien dans la BDD
                if ($infoChapitre) {
                    // * On vérifie que le chapitre de l'URL est bien lié à la publication de l'URL
                    if ($infoChapitre->getPublication() == $infoPublication) {
                        $infoPictures = $picRepo->findBy(['chapter' => $idChap]);
                        // On trouve toutes les images liées au chapitre
                        // On supprime les images de la bdd et de Cloudinary
                        foreach ($infoPictures as $picture) {
                            $url = $picture->getUrl();
                            $lastSlashPos = strrpos($url, "/");
                            $questionMarkPos = strpos($url, "?");
                            $filename = substr($url, $lastSlashPos + 1, $questionMarkPos - $lastSlashPos - 1);

                            try {
                                $this->cloudinary->uploadApi()->destroy("chapter/" . $idChap . "/" . $filename, ['invalidate' => true,]);
                            } catch (Exception $e) {
                                continue;
                            }
                            $em->remove($picture);
                            $em->flush();
                        }
                        $this->cloudinary->uploadApi()->destroy("chapter/" . $idChap, ['invalidate' => true,]);
                        $em->remove($infoChapitre);
                        $em->flush();
                    } else {
                        return $this->redirectToRoute('app_home');
                    }
                } else {
                    return $this->redirectToRoute('app_home');
                }
            } else {
                return $this->redirectToRoute('app_home');
            }
        } else {
            return $this->redirectToRoute('app_home');
        }
        return $this->redirectToRoute('app_publication_edit', ['id' => $idPub]);
    }

    #[Route('/story/edit/{idPub}/trash/delete', name: 'app_publication_del_trash_chapter')]
    public function DelAllChapter(PublicationRepository $pubRepo, PicturesRepository $picRepo, PublicationChapterRepository $pcRepo, EntityManagerInterface $em, $idPub = null): response
    {
        // * Si l'utilisateur est connecté, que la publication existe
        if ($this->getUser() && $pubRepo->find($idPub)) {
            // on récupère les informations de la publication
            $infoPublication = $pubRepo->find($idPub);
            // * Si l'utilisateur est bien l'auteur de la publication, on continue, sinon on est redirigé vers la page d'accueil
            if ($this->getUser() == $infoPublication->getUser()) {

                // On récupère tous les chapitres en statut 0
                $infoChapitres = $pcRepo->findBy(['publication' => $idPub, 'status' => 0]);
                // On les supprime tous
                foreach ($infoChapitres as $infoChapitre) {
                    $idChap = $infoChapitre->getId();
                    $infoPictures = $picRepo->findBy(['chapter' => $idChap]);
                    // On trouve toutes les images liées au chapitre
                    // On supprime les images de la bdd et de Cloudinary
                    foreach ($infoPictures as $picture) {
                        $url = $picture->getUrl();
                        $lastSlashPos = strrpos($url, "/");
                        $questionMarkPos = strpos($url, "?");
                        $filename = substr($url, $lastSlashPos + 1, $questionMarkPos - $lastSlashPos - 1);

                        try {
                            $this->cloudinary->uploadApi()->destroy("chapter/" . $idChap . "/" . $filename, ['invalidate' => true,]);
                        } catch (Exception $e) {
                            continue;
                        }
                        $em->remove($picture);
                        $em->flush();
                        $this->addFlash('success', 'Le chapitre a bien été supprimé !');
                    }
                    $this->cloudinary->uploadApi()->destroy("chapter/" . $idChap, ['invalidate' => true,]);
                    $em->remove($infoChapitre);
                    $em->flush();
                }
            } else {
                return $this->redirectToRoute('app_home');
            }
        } else {
            return $this->redirectToRoute('app_home');
        }
        return $this->redirectToRoute('app_publication_edit', ['id' => $idPub]);
    }

    #[Route('/story/chapter/autosave', name: 'app_chapter_autosave', methods: "POST")]
    public function Axios_ChapAutoSave(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PublicationChapterRepository $pcRepo, PublicationRepository $pRepo): response
    {
        $idPub = $request->get("idPub");
        $idChap = $request->get("idChap");
        //
        $dtQuill = $request->get("quill");
        $dtTitle = $request->get("title");
        //  
        $publication = $pRepo->find($idPub);
        $chapter = $pcRepo->find($idChap);
        //
        if ($publication->getId() == $chapter->getPublication()->getId() && $this->getUser() == $publication->getUser()) {
            if ($dtTitle == "") {
                // * S'il n'y a pas de titre, on lui attribue un titre par défaut qui représente "Feuille n°X", dont le x est son numéro de feuille (orderDisplay)
                $dtTitle = "Feuille n°" . $chapter->getOrderDisplay() + 1;
            }
            $chapter->setTitle(trim($dtTitle))
                ->setSlug($slugger->slug(strtolower($dtTitle)))
                ->setContent($dtQuill)
                ->setUpdated(new \DateTime('now'));
            $em->persist($chapter);
            //!SECTION
            $chapter = $pcRepo->find($idChap);
            $pcv = new PublicationChapterVersioning;
            $chapterVersioning = $pcv->setCreated(new \DateTime('now'))
                ->setTitle($chapter->getTitle())
                ->setContent($chapter->getContent())
                ->setChapter($chapter);
            $em->persist($chapterVersioning);
            $em->flush();
            //!SECTION
        } else {
            return $this->json([
                "code" => 404
            ]);
        }
        return $this->json([
            "code" => 200 // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
        ]);
    }
    #[Route('/story/chapter/publish', name: 'app_chapter_publish', methods: "POST")]
    public function Axios_Publish(Request $request, EntityManagerInterface $em, NotificationRepository $nRepo, PublicationFollowRepository $pfRepo, PublicationChapterRepository $pcRepo): response
    {
        $idPub = $request->get("idChap");
        $dataPublish = $request->get("publish");
        $chapter = $pcRepo->find($idPub);
        if ($dataPublish == "true") {
            $chapter->setStatus(2);
            $chapter->setPublished(new \DateTime('now'));
            // * Envoi d'une notification aux abonnés de la publication
            // Si le chapitre est publié et que la publication est publiée 
            if ($chapter->getPublication()->getStatus() == 2) {
                // On envoie une notification à tous les abonnés de la publication
                $followers = $pfRepo->findBy(["publication" => $chapter->getPublication()]);
                foreach ($followers as $follower) {
                    $this->notificationSystem->addNotification(7, $follower->getUser(), $chapter->getPublication()->getUser(), $chapter);
                }
            }
            //
        } else {
            $chapter->setStatus(1);
            // S'il y a des abonnées à la publication, on supprime les notifications qui leur ont été envoyées concernant le chapitre publié, désormais dépublié
            if ($chapter->getPublication()->getStatus() == 2) {
                // On cherche les notifications qui ont pour type 7 (chapitre publié) et pour publication la publication du chapitre
                $notifications = $nRepo->findBy(["type" => 7, "publication_follow" => $chapter]);
                foreach ($notifications as $notification) {
                    $em->remove($notification);
                }
            }
        }
        $em->persist($chapter);
        $em->flush();
        return $this->json([
            "code" => $dataPublish
        ]);
    }
    #[Route('/story/chapter/getversion', name: 'app_chapter_getversion', methods: "POST")]
    public function Axios_ChapterVersioning(Request $request, PublicationChapterVersioningRepository $pcvRepo): response
    {
        $idPub = $request->get("idPub");
        $idChap = $request->get("idChap");
        $dataVersion = $request->get("version");
        $pcv = $pcvRepo->find($dataVersion);

        return $this->json([
            "content" => $pcv->getContent()
        ]);
    }
    #[Route('/story/chapter/getlastversion', name: 'app_chapter_getlastversion', methods: "POST")]
    public function Axios_ChapterLastVersion(Request $request, PublicationChapterVersioningRepository $pcvRepo): response
    {
        $idChap = $request->get("idChap");
        $pcv = $pcvRepo->find($idChap);

        return $this->json([
            "content" => $pcv
        ]);
    }
    #[Route('/story/chapter/sort', name: 'app_chapter_sort', methods: "POST")]
    public function Axios_ChapSort(Request $request, EntityManagerInterface $em, PublicationChapterRepository $pcRepo): response
    {
        $idChap = $request->get("idChap");
        $order = $request->get("order");
        //
        $chapter = $pcRepo->find($idChap);
        //
        $chapter->setOrderDisplay($order);
        $em->persist($chapter);
        $em->flush();
        //
        //
        return $this->json([
            "code" => 200, // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
            "order" => $chapter->getOrderDisplay() + 1, // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
            "status" => $chapter->getStatus(), // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
        ], 200);
    }
    #[Route('/story/chapter/status', name: 'app_chapter_status', methods: "POST")]
    public function Axios_ChapStatus(Request $request, EntityManagerInterface $em, NotificationRepository $nRepo, PublicationChapterRepository $pcRepo, PublicationFollowRepository $pfRepo): response
    {
        $idChap = $request->get("idChap");

        $newStatus = $request->get("status");

        $chapter = $pcRepo->find($idChap);

        // * Envoi d'une notification aux abonnés de la publication
        // Si le chapitre est dépublié, que le $status est sur 2 et que la publication est publiée 
        if ($chapter->getStatus() == 1 && $newStatus == 2 && $chapter->getPublication()->getStatus() == 2) {
            // On envoie une notification à tous les abonnés de la publication
            $followers = $pfRepo->findBy(["publication" => $chapter->getPublication()]);
            foreach ($followers as $follower) {
                $this->notificationSystem->addNotification(7, $follower->getUser(), $chapter->getPublication()->getUser(), $chapter);
            }
        } elseif ($chapter->getStatus() == 2 && $newStatus == 1 && $chapter->getPublication()->getStatus() == 2) {
            // S'il y a des abonnées à la publication, on supprime les notifications qui leur ont été envoyées concernant le chapitre publié, désormais dépublié
            // On cherche les notifications qui ont pour type 7 (chapitre publié) et pour publication la publication du chapitre
            $notifications = $nRepo->findBy(["type" => 7, "publication_follow" => $chapter]);
            foreach ($notifications as $notification) {
                $em->remove($notification);
            }
        }
        // 
        //
        if ($chapter->getStatus() > 0 && $newStatus == 0) {
            $chapter->setTrashAt(new \DateTimeImmutable('now'));
        } elseif ($chapter->getStatus() == 0 && $newStatus == 0) {
        } else {
            $chapter->setTrashAt(null);
        }
        //
        $chapter->setStatus($newStatus);
        $em->persist($chapter);
        $em->flush();
        return $this->json([
            "status" => $newStatus,
            "order" => $chapter->getOrderDisplay() + 1, // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
            // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
        ], 200);
    }
    #[Route('/story/chapter/addimg', name: 'app_chapter_add_img', methods: "POST")]
    public function Axios_addImg(Request $request, UserRepository $userRepo, PublicationChapterRepository $pcRepo): response
    {
        $chapter = $pcRepo->find($request->get("id"));
        if ($this->getUser() == $chapter->getPublication()->getUser()) {
            $dtPic = $request->files->get("pic");
            $idChap = $request->get("id");
            return $this->uploadImage->UploadImage($dtPic, "chapter", $idChap, 1680, 600);
        } else {
            return $this->json([
                "code" => 404
            ]);
        }
    }
    #[Route('/story/chapter/deleteimg', name: 'app_chapter_delete_img', methods: "POST")]
    public function Axios_delImg(Request $request, EntityManagerInterface $em, PicturesRepository $picRepo, PublicationChapterRepository $pcRepo): response
    {
        $chapter = $pcRepo->find($request->get("id"));
        if ($this->getUser() == $chapter->getPublication()->getUser()) {
            $dtPic = $request->get("pic");
            $idChap = $request->get("id");
            // On recherche l'image dans la bdd via son "pic"
            $result = $picRepo->findOneBy(["url" => $dtPic]);
            // On récupère le nom du fichier entre "/" et "?"
            $url = $result->getUrl();
            $lastSlashPos = strrpos($url, "/");
            $questionMarkPos = strpos($url, "?");
            $filename = substr($url, $lastSlashPos + 1, $questionMarkPos - $lastSlashPos - 1);
            try {
                $this->cloudinary->uploadApi()->destroy("chapter/" . $idChap . "/" . $filename, ['invalidate' => true,]);
            } catch (Exception $e) {
                return $this->json([], 404);
            }
            // On supprime l'image de la bdd
            $em->remove($result);
            $em->flush();
            // retour
            return $this->json([], 200);
        } else {
            return $this->json([], 404);
        }
    }
}
