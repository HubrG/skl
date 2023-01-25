<?php

namespace App\Controller\Publication;

use App\Entity\PublicationChapter;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Entity\PublicationChapterVersioning;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationChapterVersioningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChapterController extends AbstractController
{


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
                        $pc = new PublicationChapter;
                        $publicationChapter = $pc->setCreated(new \DateTime('now'))
                            ->setStatus(0) // 0 = brouillon / 1 = en cours de rédaction
                            ->setTitle("Chapitre sans titre")
                            ->setPublication($infoPublication);
                        $em->persist($publicationChapter);
                        // ! on l'ajoute au versioning
                        $pcv = new PublicationChapterVersioning;
                        $publicationChapterVersioning = $pcv->setCreated(new \DateTime('now'))
                            ->setChapter($publicationChapter);
                        $em->persist($publicationChapterVersioning);
                        $em->flush();
                    }
                    // * On récupère le chapitre qui vient d'être crée
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
                            // * Si le chapitre est sur un autre status que 0

                            elseif ($infoChapitre->getStatus() > 0) {
                                //
                            }
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
    public function Axios_AutoSave(Request $request, EntityManagerInterface $em, PublicationChapterRepository $pcRepo, PublicationRepository $pRepo): response
    {
        $idPub = $request->get("idPub");
        $idChap = $request->get("idChap");
        //
        $dtQuill = $request->get("quill");
        $dtTitle = $request->get("title");
        //  
        $publication = $pRepo->find($idPub);
        $chapter = $pcRepo->find($idChap);
        if ($publication->getId() == $chapter->getPublication()->getId() && $this->getUser() == $publication->getUser()) {
            $pcv = new PublicationChapterVersioning;
            $chapter->setTitle($dtTitle);
            $chapter->setContent($dtQuill);
            $em->persist($chapter);
            $em->flush();
            //!SECTION
            $chapter = $pcRepo->find($idChap);
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
    public function Axios_Publish(Request $request, EntityManagerInterface $em, PublicationChapterRepository $pcRepo): response
    {
        $idPub = $request->get("idChap");
        $dataPublish = $request->get("publish");
        $publication = $pcRepo->find($idPub);
        if ($dataPublish == "true") {
            $publication->setStatus(2);
        } else {
            $publication->setStatus(1);
        }
        $em->persist($publication);
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
}
