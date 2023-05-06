<?php

namespace App\Controller;

use App\Entity\PublicationAnnotation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\PublicationAnnotationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AnnotationController extends AbstractController
{
    #[Route('/save-annotation', name: 'save_annotation', methods: ['POST'])]
    public function saveAnnotation(Request $request, EntityManagerInterface $em): Response
    {

        $data = json_decode($request->getContent(), true);

        $annotation = new PublicationAnnotation();
        $annotation->setAnnotationClass($data['annotation_class']);
        $annotation->setUser($this->getUser());
        $annotation->setContent(trim($data['content']));


        $em->persist($annotation);
        $em->flush();

        return $this->json([
            'code' => 200,
            'message' => $data['content']
        ], 200);
    }

    #[Route('/annotations', name: 'api_annotations', methods: ['GET'])]

    public function getAnnotations(PublicationAnnotationRepository $annotationRepository): JsonResponse
    {
        $annotations = $annotationRepository->findAll();
        $annotationsArray = [];
        foreach ($annotations as $annotation) {
            $annotationsArray[] = [
                'id' => $annotation->getId(),
                'content' => $annotation->getContent(),
                'startOffset' => $annotation->getStartOffset(),
                'endOffset' => $annotation->getEndOffset(),
                'annotationClass' => $annotation->getAnnotationClass(),
            ];
        }
        return new JsonResponse($annotationsArray);
    }
}
