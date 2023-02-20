<?php

namespace App\Controller\Publication;

use DirectoryIterator;
use Cloudinary\Uploader;
use Cloudinary\Cloudinary;
use App\Entity\Publication;
use App\Form\PublicationType;
use App\Entity\PublicationKeyword;
use Cloudinary\Transformation\Resize;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationKeywordRepository;
use App\Repository\PublicationCategoryRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class PublicationController extends AbstractController
{


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
                $publication->setType(0);
                $publication->setMature(0);
                $em->persist($publication);
                $em->flush();
                $brouillon = $pubRepo->findOneBy(["user" => $this->getUser(), "status" => 0]);
            }
            $form = $this->createForm(PublicationType::class, $brouillon);
        } else {
            return $this->redirectToRoute("app_register");
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
        return $this->renderForm('publication/add_publication.html.twig', [
            'form' => $form,
            'pub' => $brouillon
        ]);
    }
    #[Route('/story/edit/{id}', name: 'app_publication_edit')]
    public function EditPublication(PublicationRepository $pubRepo, $id = null): Response
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
                if ($this->getUser() === $publication->getUser()) {
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
            if ($publication->getUser() === $this->getUser()) {
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
    public function Axios_Publish(Request $request, PublicationKeywordRepository $keyRepo, PublicationRepository $pubRepo, EntityManagerInterface $em): Response
    {
        $dataPub = $request->get("pub");
        $dataPublish = json_decode($request->get("publish"));
        //
        $publication = $pubRepo->find($dataPub);
        //
        if ($this->getUser() == $publication->getUser()) {
            if ($dataPublish) {
                $return = 200;
                $publication->setStatus(2);
                $publication->setPublishedDate(new \DateTime('now'));
                $keywords = $publication->getPublicationKeywords();
                foreach ($keywords as $key) {
                    $countKey = $key->getCount() + 1;
                    $key->setCount($countKey);
                    $em->persist($key);
                    $em->flush();
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
    public function DeletePublication(Request $request, PublicationRepository $pubRepo, PublicationChapterRepository $pcRepo, EntityManagerInterface $em, $id = null): Response
    {
        $publication = $pubRepo->find($id);
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
            $cloudinary = new Cloudinary(
                [
                    'cloud' => [
                        'cloud_name' => 'djaro8nwk',
                        'api_key'    => '716759172429212',
                        'api_secret' => 'A35hPbZP0NsjnMKrE9pLR-EHwiU',
                    ],
                ]
            );
        }
        if ($publication->getCover()) {
            preg_match("/\/([^\/]*\.img)/", $publication->getCover(), $matches);
            $result = $matches[1];
            $cloudinary->uploadApi()->destroy("story/" . $id . "/" . $result, ['invalidate' => true,]);
            $cloudinary->adminApi()->deleteFolder("story/" . $id);
        }

        $em->remove($publication);
        $em->flush();
        return $this->redirectToRoute("app_user_show_publications");
    }
    #[Route('/story/autosave', name: 'app_publication_autosave', methods: "POST")]
    public function Axios_AutoSave(Request $request, MimeTypesInterface $mimeTypes, EntityManagerInterface $em, SluggerInterface $slugger, PublicationCategoryRepository $catRepo, PublicationRepository $pRepo): response
    {
        $idPub = $request->get("idPub");
        //
        $dtTitle = $request->get("title");
        $dtSummary = $request->get("summary");
        $dtCategory = $request->get("category");
        $dtMature = $request->get("mature");
        $dtCover = $request->files->get("cover");
        //
        $pub = $pRepo->find($idPub);
        $category = $catRepo->find($dtCategory);
        //
        $urlCloudinary = "";
        if ($this->getUser() == $pub->getUser()) {
            //
            $publication = $pub->setTitle($dtTitle)->setSlug($slugger->slug(strtolower($dtTitle)))
                ->setSummary($dtSummary)
                ->setCategory($category)
                ->setMature($dtMature)
                ->setUpdated(new \DateTime('now'));

            // * Traitement de l'image
            //! Le ficheir est-il une image ?
            $file = new File($dtCover);
            $mimeType = $mimeTypes->guessMimeType($file->getPathname());
            $isImage = strpos($mimeType, 'image/') === 0;
            if (!$isImage) {
                // Fichier n'est une image
                return $this->json([
                    "code" => 500,
                    "value" => "Le fichier que vous avez envoyé n'est pas une image."
                ]);
            }
            //
            if ($dtCover) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/images/uploads/story/' . $idPub;
                $newFilename = $pub->getId() . rand(0, 9999) . '.img';

                try {
                    $dtCover->move(
                        $destination,
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $this->json([
                        "code" => 500,
                        "value" => "Une erreur est survenue. Vérifiez que le fichier n'est pas corrompu, sinon préférez un format JPG"
                    ]);
                }
                $cloudinary = new Cloudinary(
                    [
                        'cloud' => [
                            'cloud_name' => 'djaro8nwk',
                            'api_key'    => '716759172429212',
                            'api_secret' => 'A35hPbZP0NsjnMKrE9pLR-EHwiU',
                        ],
                    ]
                );

                $cloudinary->uploadApi()->upload(
                    $destination . "/" . $newFilename,
                    ['public_id' => $newFilename, 'folder' => "story/" . $idPub,]
                );

                $urlCloudinary = $cloudinary->image("story/" . $idPub . "/" . $newFilename)->resize(Resize::fill(529, 793))->toUrl();
                // * On supprime la cover
                if (\file_exists($destination . "/" . $newFilename)) {
                    \unlink($destination . "/" . $newFilename);
                }
                // On récupère le dernier cover de la publication pour la supprimer de Cloudinary : 
                if ($pub->getCover()) {
                    preg_match("/\/([^\/]*\.img)/", $pub->getCover(), $matches);
                    $result = $matches[1];
                    $cloudinary->uploadApi()->destroy("story/" . $idPub . "/" . $result, ['invalidate' => true,]);
                }
                $publication->setCover($urlCloudinary);
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
            "cloudinary" => $urlCloudinary // dataName = permet de n'afficher qu'une seule fois le message de sauvegarde
        ]);
    }
}
