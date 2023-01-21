<?php

namespace App\Controller\Publication;

use App\Entity\Publication;
use App\Form\PublicationType;
use App\Entity\PublicationKeyword;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationKeywordRepository;
use App\Repository\PublicationCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicationController extends AbstractController
{
    #[Route('/story/add', name: 'app_publication_add')]
    public function index(Request $request, PublicationRepository $pubRepo, EntityManagerInterface $em): Response
    {
        // Si l'utilisateur est connecté...
        if ($this->getUser()) {
            // GESTION DU BROUILLON
            // On récupère son dernier brouillon, s'il existe
            $brouillon = $pubRepo->findOneBy(["user" => $this->getUser(), "status" => 0]);
            if ($brouillon) {
                $form = $this->createForm(PublicationType::class, $brouillon);
            } else // Sinon, on crée une nouvelle ligne de brouillon

            {
                $publication = new Publication();
                $publication->setUser($this->getUser());
                $publication->setStatus(0);
                $publication->setType(0);
                $publication->setMature(0);
                $em->persist($publication);
                $em->flush();
                $brouillon = $pubRepo->findOneBy(["user" => $this->getUser(), "status" => 0]);
                $form = $this->createForm(PublicationType::class, $brouillon);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
        // GESTION DU NEXT STEP
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // on modifie le statut du post => (1) ce n'est plus un brouillon
            $status = $pubRepo->findOneBy(["user" => $this->getUser(), "status" => 0]);
            $status->setStatus(1)
                ->setUpdated(new \DateTime('now'))
                ->setCreated(new \DateTime('now'));
            //si le titre est vide...
            if ($form->get("title")->getViewData() === "") {
                $status->setTitle("Récit sans titre");
            } else {
                $status->setTitle(trim(ucfirst($form->get("title")->getViewData())));
            }
            // On formate le summary
            $status->setSummary(trim(ucfirst($form->get("summary")->getViewData())));
            $em->persist($form->getData());
            $em->persist($status);
            $em->flush();
            //
            return $this->redirectToRoute("app_publication_edit", ["id" => $brouillon->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('publication/add_publication.html.twig', [
            'form' => $form,
            'pub' => $brouillon
        ]);
    }
    #[Route('/story/edit/{id}', name: 'app_publication_edit')]
    public function editPubChapter(PublicationRepository $pubRepo, $id = null): Response
    {
        if ($this->getUser()) {
            $infoPublication = $pubRepo->findOneBy(["user" => $this->getUser(), "id" => $id]);
            if ($infoPublication) {
                $formPub = $this->createForm(PublicationType::class, $infoPublication);
                return $this->renderForm('publication/edit_publication.html.twig', [
                    'infoPub' => $infoPublication,
                    'formPub' => $formPub
                ]);
            } else {
                return $this->redirectToRoute("app_home");
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // ROUTES PERMETTANT LA GESTION DE DONÉES EN BACKGROUND (ADD KEYWORD / DEL KEYWORD / AUTOSAVE)
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    #[Route('story/add_key/{pub<\d+>?0}/{value}', methods: 'POST', name: 'app_publication_add_keyword')]
    public function addkey(PublicationKeywordRepository $keyRepo, PublicationRepository $pubRepo, EntityManagerInterface $em, $pub = null, $value = null): Response
    {
        // Si value est set et que l'utilisateur est connecté...
        if ($value && $this->getUser()) {
            $value = trim(ucfirst($value));
            $keyExists = $keyRepo->findOneBy(["keyword" => $value]);
            $publication = $pubRepo->find($pub);
            // Si le post existe
            if ($publication) {
                // Si l'utilisateur connecté est bien l'auteur du post, on poursuit...
                if ($this->getUser() === $publication->getUser()) {
                    // Si le mot clé existe déjà...
                    if ($keyExists) {
                        // ... alors on ne le recrée pas et on lui ajoute 1 occurrence
                        $countKey = $keyExists->getCount() + 1;
                        $key = $keyExists->setCount($countKey)
                            // on ajoute le mot au ManyToMany de l'article correspondant
                            ->addPublication($publication);
                        $em->persist($key);
                        $em->flush();
                        return $this->json(["code" => "200", "value" => $value]);
                    }
                    // sinon, on cré le nouveau mot et on l'ajoute au ManyToMany de l'article...

                    else {
                        $keykey = new PublicationKeyword();
                        $key = $keykey->setKeyword($value)
                            ->setCount(1)
                            ->addPublication($publication);
                        $em->persist($key);
                        $em->flush();
                        return $this->json(["code" => "200", "value" => $value]);
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
    #[Route('story/{mode}/del_key/{pub<\d+>?0}/{value}', name: 'app_publication_del_keyword')]
    public function delkey(PublicationKeywordRepository $keyRepo, PublicationRepository $pubRepo, EntityManagerInterface $em, $pub = null, $value = null, $mode = null): Response
    {
        // Si value est set et que l'utilisateur est connecté...
        if ($value && $this->getUser()) {
            $delKey = $keyRepo->findOneBy(["keyword" => $value]);
            $publication = $pubRepo->find($pub);
            // Si l'user authentitifé est bien l'auteur du post...
            if ($publication->getUser() === $this->getUser()) {
                // Si le mot existe alors...
                if ($delKey) {
                    // On verifie que le post existe et que ce keyword est bien lié au post
                    if ($publication && $delKey->getPublication()) {
                        // On décrémente le keyword dissocié
                        $countKey = $delKey->getCount() - 1;
                        $delKey->removePublication($publication)
                            ->setCount($countKey);
                        $em->persist($delKey);
                        $em->flush();
                        // le cas échéant, on le supprime s'il est à 0
                        if ($countKey === 0) {
                            $em->remove($delKey);
                            $em->flush();
                        }
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
    #[Route('/story/as/{pub}', methods: 'POST', name: 'app_publication_autosave')]
    public function aspost(Request $request, PublicationCategoryRepository $catRepo, PublicationRepository $pubRepo, EntityManagerInterface $em, $pub = null): Response
    {
        $dataName = $request->get("name");
        $dataValue = $request->get("value");
        $dataFileName = $request->get("filename");
        $dataFile = $request->files->get("file");
        //
        $publication = $pubRepo->find($pub);
        //
        if ($dataName === "publication[title]") {
            if ($dataValue === "") {
                $dataValue = "Récit sans titre";
            }
            $publication->setTitle(trim(ucfirst($dataValue)));
        }
        if ($dataName === "publication[cover]") {
            // Si le fichier est bien une image, on execute

            $destination = $this->getParameter('kernel.project_dir') . '/public/images/uploads/story/' . $pub;
            // si une cover a déjà été envoyée, alors on la supprime pour la remplacer par la nouvelle
            if ($publication->getCover()) {
                \unlink($destination . "/" . $publication->getCover());
            }
            $newFilename = $dataFileName . '.jpg';
            $dataFile->move(
                $destination,
                $newFilename
            );
            $publication->setCover($newFilename);
        }
        if ($dataName === "publication[summary]") {
            $publication->setSummary(trim(ucfirst($dataValue)));
        }
        if ($dataName === "publication[category]") {
            $catR = $catRepo->find($dataValue);
            $publication->setCategory($catR);
        }
        if ($dataName === "publication[mature]") {
            $publication->setMature($dataValue);
        }
        $publication->setUpdated(new \DateTime('now'));
        $em->persist($publication);
        $em->flush();
        //
        return $this->json([
            "code" => "200"
        ]);
    }
    #[Route('/story/publish', methods: 'POST', name: 'app_publication_publish')]
    public function publish(Request $request, PublicationRepository $pubRepo, EntityManagerInterface $em): Response
    {
        $dataPub = $request->get("pub");
        $dataPublish  = json_decode($request->get("publish"));
        //
        $publication = $pubRepo->find($dataPub);
        //
        if ($this->getUser() == $publication->getUser()) {
            if ($dataPublish) {
                $return = 2;
                $publication->setStatus(2);
                $publication->setPublishedDate(new \DateTime('now'));
            } else {
                $publication->setStatus(1);
                $return = 1;
            }
            $em->persist($publication);
            $em->flush();
            return $this->json([
                "code" => "200", "value" => $return
            ]);
        } else {
            return $this->json([
                "code" => "500", "value" => null
            ]);
        }
        //

    }
}
