<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\PublicationAnnotation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\PublicationAnnotationRepository;
use App\Repository\PublicationChapterVersioningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AnnotationController extends AbstractController
{

    public function __construct(private PublicationAnnotationRepository $paRepo, private PublicationChapterRepository $pchRepo, private PublicationChapterVersioningRepository $pchvRepo)
    {
    }

    #[Route('/save-annotation', name: 'save_annotation', methods: ['POST'])]
    public function saveAnnotation(Request $request, EntityManagerInterface $em): Response
    {

        $data = json_decode($request->getContent(), true);
        // On recherche le chapitre
        $chapter = $this->pchRepo->find($data['chapter']);
        // On supprime les annotations précédentes relatives au chapitre

        if (!$this->getUser()) {
            return $this->json([
                'code' => 403,
                'message' => "Vous devez être connecté pour pouvoir annoter"
            ]);
        }

        if ($data['version'] == "") {
            // On recherche la dernière version du chapitre 
            $version = $this->pchvRepo->findOneBy(['chapter' => $chapter], ['id' => 'DESC']);
        } else {
            // On recherche la version du chapitre correspondant à la version donnée
            $version = $this->pchvRepo->find($data['version']);
        }
        // On supprime les annotations de l'utilisateur avec un annotation_class NULL
        $annotations = $this->paRepo->findBy(['chapter' => $chapter, 'AnnotationClass' => NULL, 'user' => $this->getUser()]);
        foreach ($annotations as $annotation) {
            $em->remove($annotation);
            $em->flush(); // Déplacez cette ligne à l'intérieur de la boucle
        }

        $annotation = new PublicationAnnotation();
        $annotation->setAnnotationClass($data['annotation_class']);
        $annotation->setUser($this->getUser());
        $annotation->setColor($data['color']);
        $annotation->setMode($data['mode'] == "mark-for-me" ? 0 : 1);
        $annotation->setComment($data['comment']);
        $annotation->setContentPlain($data['content_plain']);
        $annotation->setVersion($version);
        $annotation->setChapter($chapter);
        $annotation->setCreatedAt(new DateTimeImmutable());
        $annotation->setContent(trim($data['content']));


        $em->persist($annotation);
        $em->flush();


        return $this->json([
            'code' => 200,
            'message' => $data['content']
        ], 200);
    }
    #[Route('/delete-annotation', name: 'delete_annotation', methods: ['POST'])]
    public function deleteAnnotation(Request $request, PublicationAnnotationRepository $paRepo, EntityManagerInterface $em, PublicationChapterRepository $pchRepo): Response
    {

        $data = json_decode($request->getContent(), true);
        // On cherche le chapitre
        $chapter = $pchRepo->find($data['chapter']);
        $class = $data['annotation_class'];
        // On supprime toutes les annotations du chapitre avec la classe donnée si l'utilisteur est propriétaire de l'annotation
        $annotations = $paRepo->findBy(['chapter' => $chapter, 'AnnotationClass' => $class]);
        foreach ($annotations as $annotation) {
            if ($annotation->getUser() == $this->getUser()) {
                $em->remove($annotation);
                $em->flush(); // Déplacez cette ligne à l'intérieur de la boucle
            }
        }
        // On supprime les annotation avec une annotation_clas NULL
        $annotations = $paRepo->findBy(['chapter' => $chapter, 'AnnotationClass' => NULL, 'user' => $this->getUser()]);
        foreach ($annotations as $annotation) {
            $em->remove($annotation);
            $em->flush(); // Déplacez cette ligne à l'intérieur de la boucle
        }

        // S'il n'y a plus d'annotation avec classe pour ce chapitre et cet utilisateur, on supprime toutes les entrées de l'utilisateur pour ce hcapitre
        $annotations = $paRepo->findBy(['chapter' => $chapter, "mode" => 0, 'user' => $this->getUser()]);
        // On recherche la version
        $version = $this->pchvRepo->find($data['version']);



        if (count($annotations) > 0) {

            // On enregistre le nouvel article
            $annotation = new PublicationAnnotation();
            // $annotation->setAnnotationClass($data['annotation_class']);
            $annotation->setUser($this->getUser());
            $annotation->setMode($data['mode'] == "mark-for-me" ? 0 : 1);
            // $annotation->setColor($data['color']);
            // $annotation->setContentPlain($data['content_plain']);
            $annotation->setVersion($version);
            $annotation->setCreatedAt(new DateTimeImmutable());
            $annotation->setChapter($chapter);
            $annotation->setContent(trim($data['content']));


            $em->persist($annotation);
            $em->flush();
        }

        return $this->json([
            'code' => 200,
            'message' => "Annotation supprimée"
        ], 200);
    }
    #[Route('/delete-review', name: 'delete_review', methods: ['POST'])]
    public function deleteReview(Request $request, PublicationAnnotationRepository $paRepo, EntityManagerInterface $em, PublicationChapterRepository $pchRepo): Response
    {

        $data = json_decode($request->getContent(), true);
        // On cherche le chapitre
        $chapter = $pchRepo->find($data['chapter']);
        $class = $data['annotation_class'];
        // On vériifie que l'utilisateur est bien le propriétaire de l'annotation
        $annotation = $paRepo->findOneBy(['chapter' => $chapter, 'AnnotationClass' => $class, 'user' => $this->getUser()]);
        if (!$annotation) {
            return $this->json([
                'code' => 403,
                'message' => "Vous n'êtes pas autorisé à supprimer cette annotation"
            ], 403);
        }
        // On supprime toutes les annotations du chapitre avec la classe donnée si l'utilisteur est propriétaire de l'annotation
        $annotations = $paRepo->findBy(['chapter' => $chapter, 'AnnotationClass' => $class]);
        foreach ($annotations as $annotation) {
            if ($annotation->getUser() == $this->getUser()) {
                $em->remove($annotation);
                $em->flush(); // Déplacez cette ligne à l'intérieur de la boucle
            }
        }
        // On supprime les annotation avec une annotation_clas NULL
        $annotations = $paRepo->findBy(['chapter' => $chapter, 'AnnotationClass' => NULL, 'user' => $this->getUser()]);
        foreach ($annotations as $annotation) {
            $em->remove($annotation);
            $em->flush(); // Déplacez cette ligne à l'intérieur de la boucle
        }

        // S'il n'y a plus d'annotation avec classe pour ce chapitre et cet utilisateur, on supprime toutes les entrées de l'utilisateur pour ce hcapitre
        $annotations = $paRepo->findBy(['chapter' => $chapter, "mode" => 1]);

        // On cherche la version 
        $version = $this->pchvRepo->find($data['version']);
        if (count($annotations) > 0) {

            // On enregistre le nouvel article
            $annotation = new PublicationAnnotation();
            // $annotation->setAnnotationClass($data['annotation_class']);
            $annotation->setUser($this->getUser());
            $annotation->setMode($data['mode'] == "mark-for-me" ? 0 : 1);
            $annotation->setVersion($version);
            // $annotation->setColor($data['color']);
            // $annotation->setContentPlain($data['content_plain']);
            $annotation->setCreatedAt(new DateTimeImmutable());
            $annotation->setChapter($chapter);
            $annotation->setContent(trim($data['content']));


            $em->persist($annotation);
            $em->flush();
        }

        return $this->json([
            'code' => 200,
            'message' => "Annotation supprimée"
        ], 200);
    }
    #[Route('/save-review', name: 'save_review', methods: ['POST'])]
    public function saveReview(Request $request, EntityManagerInterface $em, PublicationChapterRepository $pchRepo): Response
    {

        $data = json_decode($request->getContent(), true);
        // On recherche le chapitre
        $chapter = $pchRepo->find($data['chapter']);
        // On supprime les annotations précédentes relatives au chapitre


        if (!$this->getUser()) {
            return $this->json([
                'code' => 403,
                'message' => "Vous devez être connecté pour pouvoir annoter"
            ]);
        }

        // On supprime les annotations de tous les utilisateurs avec un annotation_class NULL
        $annotations = $this->paRepo->findBy(['chapter' => $chapter, 'AnnotationClass' => NULL]);
        foreach ($annotations as $annotation) {
            $em->remove($annotation);
            $em->flush(); // Déplacez cette ligne à l'intérieur de la boucle
        }

        $annotation = new PublicationAnnotation();
        $annotation->setAnnotationClass($data['annotation_class']);
        $annotation->setUser($this->getUser());
        $annotation->setColor($data['color']);
        $annotation->setMode($data['mode'] == "mark-for-me" ? 0 : 1);
        $annotation->setContentPlain($data['content_plain']);
        $annotation->setChapter($chapter);
        $annotation->setCreatedAt(new DateTimeImmutable());
        $annotation->setContent(trim($data['content']));


        $em->persist($annotation);
        $em->flush();


        return $this->json([
            'code' => 200,
            'message' => $data['content']
        ], 200);
    }
    public function getAnnotation($chapter, $mode, $versionSet)
    {

        // On récupère le chapitre 
        $chapter = $this->pchRepo->find($chapter);
        if (!$versionSet) {
            // On récupère la dernière version du chapitre dans pchvRepo
            $version = $this->pchvRepo->findOneBy(["chapter" => $chapter], ['id' => 'DESC']);
            $version = $version->getId();
        } else {
            $version = $versionSet;
        }

        if ($mode == "mark-for-me") {
            // On récupère la dernière annotation de l'utilisateur pour le chapitre
            $annotation = $this->paRepo->findOneBy(['chapter' => $chapter, 'user' => $this->getUser(), 'mode' => 0, "version" => $version], ['id' => 'DESC']);
            // On ne garde que la dernière annotation
            if ($annotation) {
                $annotation = $annotation->getContent();
            } else {
                $annotation = "";
            }
        } else {
            // On récupère la dernière annotation de l'utilisateur pour le chapitre
            $annotation = $this->paRepo->findOneBy(['chapter' => $chapter, 'mode' => 1, "version" => $version], ['id' => 'DESC']);
            // On ne garde que la dernière annotation
            if ($annotation) {
                $annotation = $annotation->getContent();
            } else {
                $annotation = "";
            }
        }
        // On retourne l'annotation
        return $annotation;
    }
    #[Route('/reload-revision', name: 'reload_revision', methods: ['POST'])]
    public function reloadArticle(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $content = $this->getAnnotation($data['chapter'], 1, $data['version']);
        // On supprime les balises images de $chapterContent avec une regex


        return $this->json([
            'code' => 200,
            'message' => trim($content),
            'compar' => trim($data['article']),
            'chapter' => $data['chapter'],
            'version' => $data['version'],
        ], 200);
    }
}
