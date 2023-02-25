<?php

namespace App\Controller;

use App\Entity\PublicationCommentLike;
use App\Services\PublicationPopularity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationCommentRepository;
use App\Repository\PublicationChapterCommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    private $publicationPopularity;
    public function __construct(EntityManagerInterface $em, PublicationPopularity $publicationPopularity)
    {
        $this->publicationPopularity = $publicationPopularity;
    }
    #[Route('/comment/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function CommentDelete(Request $request, EntityManagerInterface $em, PublicationCommentRepository $pcomRepo): Response
    {
        $id = $request->request->get('id');
        $pcom = $pcomRepo->find($id);

        // Si l'utilisateur est bien le propriétaire du commentaire
        if ($pcom && $pcom->getUser() == $this->getUser()) {
            $em->remove($pcom);
            $em->flush();
            //
            $this->publicationPopularity->PublicationPopularity($pcom->getPublication());
            //
        } else {
            return $this->json([
                'code' => 403,
                'success' => true,
                'message' => 'Vous n\'avez pas le droit de supprimer ce commentaire'
            ], 403);
        }
        return $this->json([
            'code' => 200,
            'success' => true,
            'message' => 'Commentaire supprimé'
        ], 200);
    }
    #[Route('/comment/like', name: 'app_comment_like', methods: ['POST'])]
    public function CommentLike(Request $request, PublicationCommentRepository $pccRepo, EntityManagerInterface $em): response
    {
        $id = $request->get("id");
        // * On récupère le commentaire
        $comment = $pccRepo->find($id);
        // * Si le commentaire existe et que l'auteur du commentaire n'est pas l'auteur du like
        if ($comment and $comment->getUser() != $this->getUser()) {
            // * On vérifie que le commentaire n'a pas déjà été liké par l'utilisateur
            $like = $comment->getPublicationCommentLikes()->filter(function ($like) {
                return $like->getUser() == $this->getUser();
            })->first();
            // * Si le commentaire a déjà été liké, on supprime le like
            if ($like) {
                $em->remove($like);
                $em->flush();
                // * On met à jour la popularité de la publication
                $this->publicationPopularity->PublicationPopularity($comment->getPublication());
                //
                return $this->json([
                    'code' => 200,
                    'message' => 'Le like a bien été supprimé.'
                ], 200);
            }
            // * Sinon, on ajoute le like
            $like = new PublicationCommentLike();
            $like->setUser($this->getUser())
                ->setComment($comment)
                ->setCreatedAt(new \DateTimeImmutable());
            $em->persist($like);
            $em->flush();
            // * On met à jour la popularité de la publication
            $this->publicationPopularity->PublicationPopularity($comment->getPublication());
            //
        } else {
            return $this->json([
                'code' => 403,
                'message' => 'Vous n\'avez pas les droits pour modifier ce commentaire.',
            ], 403);
        }
        //
        return $this->json([
            'code' => 201,
            'message' => 'Le like a bien été ajouté.'
        ], 201);
    }
    #[Route('/comment/update', name: 'app_comment_update', methods: ['POST'])]
    public function CommentUpdate(Request $request, PublicationCommentRepository $pcomRepo, EntityManagerInterface $em): response
    {
        $id = $request->get("id");
        $dtNewCom = $request->get("newCom");
        $comment = $pcomRepo->find($id);
        if ($comment and $comment->getUser() == $this->getUser()) {
            $comment->setContent($dtNewCom);
            $comment->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($comment);
            $em->flush();
        } else {
            return $this->json([
                'code' => 403,
                'message' => 'Vous n\'avez pas les droits pour modifier ce commentaire.',
            ], 403);
        }
        //
        return $this->json([
            'code' => 200,
            'message' => 'Le commentaire a bien été modifié.',
            'comment' => $comment->getContent()
        ], 200);
    }
}
