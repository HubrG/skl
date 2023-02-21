<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use App\Repository\UserRepository;
use Cloudinary\Transformation\Resize;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class ImageService extends AbstractController
{

    private $em;
    private $mimeTypes;

    private $userRepo;

    private $pRepo;

    public function __construct(EntityManagerInterface $em, MimeTypesInterface $mimeTypes, UserRepository $userRepo, PublicationRepository $pRep)
    {
        $this->em = $em;
        $this->mimeTypes = $mimeTypes;
        $this->userRepo = $userRepo;
        $this->pRepo = $pRep;
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
        }
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
            ['public_id' => $newFilename, 'folder' => $folder . "/" . $id,]
        );

        $urlCloudinary = $cloudinary->image($folder . "/" . $id . "/" . $newFilename)->resize(Resize::fill($x, $y))->toUrl();
        $this->DeleteImage($destination . "/" . $newFilename, $get, $id, $folder);
        // * On supprime la pp de l'utilisateur

        //!
        if ($repoSave == "profil_picture") {
            $repo->setProfilPicture($urlCloudinary);
        } elseif ($repoSave == "profil_background") {
            $repo->setProfilBackground($urlCloudinary);
        } elseif ($repoSave == "story") {
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
        $cloudinary = new Cloudinary(
            [
                'cloud' => [
                    'cloud_name' => 'djaro8nwk',
                    'api_key'    => '716759172429212',
                    'api_secret' => 'A35hPbZP0NsjnMKrE9pLR-EHwiU',
                ],
            ]
        );
        if (\file_exists($path)) {
            \unlink($path);
        }
        // On récupère le dernier cover de la publication pour la supprimer de Cloudinary : 
        if ($get) {
            preg_match("/\/([^\/]*\.img)/", $get, $matches);
            $result = $matches[1];
            $cloudinary->uploadApi()->destroy($folder . "/" . $id . "/" . $result, ['invalidate' => true,]);
        }
    }
}
