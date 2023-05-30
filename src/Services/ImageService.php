<?php

namespace App\Services;

use App\Entity\Pictures;
use Cloudinary\Cloudinary;
use App\Repository\UserRepository;
use Cloudinary\Transformation\Resize;
use App\Repository\PicturesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Repository\PublicationChapterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



class ImageService extends AbstractController
{

    private $em;
    private $mimeTypes;

    private $userRepo;

    private $pRepo;
    private $pcRepo;
    private $picRepo;

    private $cloudinary;

    public function __construct(PicturesRepository $picRepo, PublicationChapterRepository $pcRepo, Cloudinary $cloudinary, EntityManagerInterface $em, MimeTypesInterface $mimeTypes, UserRepository $userRepo, PublicationRepository $pRep)
    {
        $this->em = $em;
        $this->mimeTypes = $mimeTypes;
        $this->userRepo = $userRepo;
        $this->pRepo = $pRep;
        $this->cloudinary = $cloudinary;
        $this->picRepo = $picRepo;
        $this->pcRepo = $pcRepo;
    }

    public function UploadImage($dtImage, $repo, $id, $x, $y)
    {
        //! Le fichier est-il une image ?
        $file = new File($dtImage);
        $mimeType = $this->mimeTypes->guessMimeType($file->getPathname());
        $isImage = strpos($mimeType, 'image/') === 0;
        if (!$isImage) {
            // Fichier n'est une image
            return $this->json([
                "code" => 300,
                "value" => "Le fichier que vous avez envoyé n'est pas une image."
            ]);
        }
        //
        if ($repo == "profil_picture") {
            $repoSave = $repo;
            $repo = $this->userRepo->find($id);
            $folder = "profil_picture";
            $get = $repo->getProfilPicture();
        } elseif ($repo == "profil_background") {
            $repoSave = $repo;
            $folder = "profil_background";
            $repo = $this->userRepo->find($id);
            $get = $repo->getProfilBackground();
        } elseif ($repo == "story") {
            $repoSave = $repo;
            $folder = "story";
            $repo = $this->pRepo->find($id);
            $get = $repo->getCover();
        } elseif ($repo == "chapter") { // ! Chapitres
            $folder = "chapter";
            $destination = $this->getParameter('kernel.project_dir') . '/public/images/uploads/' . $folder . '/' . $id;
            $newFilename = $id . rand(0, 9999) . '.img';
            try {
                $dtImage->move(
                    $destination,
                    $newFilename
                );
            } catch (FileException $e) {
                return $this->json([
                    "code" => 500,
                    "value" => "Une erreur est survenue lors de l'upload de votre image."
                ]);
            }
            $this->cloudinary->uploadApi()->upload(
                $destination . "/" . $newFilename,
                ['public_id' => $newFilename, 'folder' => $folder . "/" . $id,]
            );
            $urlCloudinary = $this->cloudinary->image($folder . "/" . $id . "/" . $newFilename)->toUrl();
            $this->DeleteImage($destination . "/" . $newFilename, null, $id, $folder);
            // On ajoute l'image dans la base de données :
            $repo = $this->pcRepo->find($id);
            $pic = new Pictures();
            $pic->setChapter($repo);
            $pic->setUrl($urlCloudinary);
            $pic->setCreatedAt(new \DateTimeImmutable());
            $this->em->persist($pic);
            $this->em->flush();
            return $this->json([
                "code" => 200,
                "cloudinary" => $urlCloudinary
            ]);
        } // ! Fin 
        $destination = $this->getParameter('kernel.project_dir') . '/public/images/uploads/' . $folder . '/' . $id;
        $newFilename = $id . rand(0, 9999) . '.img';

        try {
            $dtImage->move(
                $destination,
                $newFilename
            );
        } catch (FileException $e) {
            return $this->json([
                "code" => 500,
                "value" => "Une erreur est survenue lors de l'upload de votre image."
            ]);
        }


        $this->cloudinary->uploadApi()->upload(
            $destination . "/" . $newFilename,
            ['public_id' => $newFilename, 'folder' => $folder . "/" . $id,]
        );

        $urlCloudinary = $this->cloudinary->image($folder . "/" . $id . "/" . $newFilename)->toUrl();

        $this->DeleteImage($destination . "/" . $newFilename, $get, $id, $folder);
        // * On supprime la pp de l'utilisateur
        //!
        if ($repoSave == "profil_picture") {
            $urlCloudinary = str_replace("upload/v1", "upload/c_fill,f_auto,g_auto,h_800,w_800", $urlCloudinary);
            $repo->setProfilPicture($urlCloudinary);
        } elseif ($repoSave == "profil_background") {
            $urlCloudinary = str_replace("upload/v1", "upload/c_fill,f_auto,g_auto", $urlCloudinary);
            $repo->setProfilBackground($urlCloudinary);
        } elseif ($repoSave == "story") {
            $urlCloudinary = str_replace("upload/v1", "upload/c_fill,f_auto,h_793,w_529", $urlCloudinary);
            $repo->setCover($urlCloudinary);
        }
        //
        $this->em->persist($repo);
        $this->em->flush();
        return $this->json([
            "code" => 200,
            "value" => "Votre image de profil a bien été modifiée !",
            "cloudinary" => $urlCloudinary
        ]);
    }
    public function DeleteImage($path, $get, $id, $folder)
    {

        if (\file_exists($path)) {
            \unlink($path);
        }
        // On récupère le dernier cover de la publication pour la supprimer de Cloudinary : 
        if ($get) {

            try {
                preg_match("/\/([^\/]*\.img)/", $get, $matches);
                $result = $matches[1];
            } catch (\Exception $e) {
                $result = null;
            }
            if ($result) {
                $this->cloudinary->uploadApi()->destroy($folder . "/" . $id . "/" . $result, ['invalidate' => true,]);
            }
        }
    }
}
