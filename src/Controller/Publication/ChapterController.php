<?php

namespace App\Controller\Publication;

use App\Entity\PublicationChapter;
use App\Services\NotificationSystem;
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

    private $notificationSystem;

    public function __construct(NotificationSystem $notificationSystem)
    {
        $this->notificationSystem = $notificationSystem;
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
                    $pcExists = $pcRepo->findOneBy(['publication' => $idPub, "status" => 0]);
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
                            ->setStatus(0) // 0 = brouillon / 1 = en cours de rédaction
                            ->setPublication($infoPublication)
                            ->setTitle("Feuille n°" . $nbrChap . $chapAdd)
                            ->setSlug("feuille-n0" . $nbrChap . $chapAdd)
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
                    $infoChapitre = $pcRepo->findOneBy(['publication' => $idPub, "status" => 0]);
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
                            // * Si le chapitre est en statut 0 (pré-brouillon), on le passe en statut 1 (en cours de rédaction)
                            if ($infoChapitre->getStatus() === 0) {
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
                    'infoChap' => $infoChapitre
                ]);
            } else {
                return $this->redirectToRoute('app_home');
            }
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
    #[Route('/story/edit/{idPub}/chapter/{idChap}/delete', name: 'app_publication_del_chapter')]
    public function DelChapter(PublicationRepository $pubRepo, PublicationChapterRepository $pcRepo, EntityManagerInterface $em, $idPub = null, $idChap = null): response
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
            $chapter->setTitle($dtTitle)
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
    public function Axios_ChapSort(Request $request, EntityManagerInterface $em, NotificationRepository $nRepo, PublicationChapterRepository $pcRepo, PublicationFollowRepository $pfRepo): response
    {
        $idChap = $request->get("idChap");
        $order = $request->get("order");
        $status = $request->get("status");
        //
        $chapter = $pcRepo->find($idChap);
        //
        // * Envoi d'une notification aux abonnés de la publication
        // Si le chapitre est dépublié, que le $status est sur 2 et que la publication est publiée 
        if ($chapter->getStatus() == 1 && $status == 2 && $chapter->getPublication()->getStatus() == 2) {
            // On envoie une notification à tous les abonnés de la publication
            $followers = $pfRepo->findBy(["publication" => $chapter->getPublication()]);
            foreach ($followers as $follower) {
                $this->notificationSystem->addNotification(7, $follower->getUser(), $chapter->getPublication()->getUser(), $chapter);
            }
        } elseif ($chapter->getStatus() == 2 && $status == 1 && $chapter->getPublication()->getStatus() == 2) {
            // S'il y a des abonnées à la publication, on supprime les notifications qui leur ont été envoyées concernant le chapitre publié, désormais dépublié
            // On cherche les notifications qui ont pour type 7 (chapitre publié) et pour publication la publication du chapitre
            $notifications = $nRepo->findBy(["type" => 7, "publication_follow" => $chapter]);
            foreach ($notifications as $notification) {
                $em->remove($notification);
            }
        }
        //
        $chapter->setOrderDisplay($order)
            ->setStatus($status);
        $em->persist($chapter);
        $em->flush();

        return $this->json([
            "code" => 200, // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
            "order" => $chapter->getOrderDisplay() + 1, // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
            "status" => $chapter->getStatus() // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
        ]);
    }
}
