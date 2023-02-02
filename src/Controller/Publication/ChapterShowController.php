<?php

namespace App\Controller\Publication;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PublicationChapterComment;
use App\Repository\PublicationRepository;
use App\Form\PublicationChapterCommentType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationChapterCommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChapterShowController extends AbstractController
{
    #[Route('/recit-{slugPub}/{user}/{idChap}/{slug?}', name: 'app_chapter_show')]
    public function showChapter(Request $request, PublicationChapterRepository $pchRepo, EntityManagerInterface $em, PublicationRepository $pRepo, $idChap = null, $idPub = null): response
    {
        // * On recherche le chapitre
        $chapter = $pchRepo->find($idChap);

        // ! Test des conditions pour afficher le chapitre
        // * Si le chapitre existe, on récupère la publication
        if ($chapter) {
            $publication = $pRepo->find($chapter->getPublication());
        } else {
            // * Sinon on redirige vers la page d'acceuil
            return $this->redirectToRoute("app_home");
        }
        // * Si la publication n'est pas publiée, on redirige vers la page d'acceuil — Uniquement si l'utilisateur n'est pas l'auteur de la publication
        if ($publication->getStatus() != 2 && $publication->getUser() != $this->getUser()) {
            // On redirige vers la page précédente 
            return $this->redirectToRoute("app_home");
        }
        // * Si le chapitre n'est pas publié, on redirige vers la page d'acceuil — Uniquement si l'utilisateur n'est pas l'auteur de la publication

        elseif ($chapter->getStatus() != 2 && $publication->getUser() != $this->getUser()) {
            // On redirige vers la page précédente 
            return $this->redirectToRoute("app_home");
        }
        // ! Si toutes les conditions sont réunies, on traitre les données
        $previous = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() - 1]);
        $next = $pchRepo->findOneBy(['publication' => $publication->getId(), 'status' => 2, 'order_display' => $chapter->getOrderDisplay() + 1]);
        // * 
        $form = $this->createForm(PublicationChapterCommentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setChapter($chapter);
            $comment->setUser($this->getUser());
            $comment->setPublishDate(new \DateTime('now'));
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Votre commentaire a bien été ajouté.');
            return $this->redirectToRoute('app_chapter_show', ['slugPub' => $publication->getSlug(), 'user' => $publication->getUser()->getUsername(), 'idChap' => $chapter->getId(), 'slug' => $chapter->getSlug()]);
        }
        // *
        return $this->render('publication/show_chapter.html.twig', [
            'infoPub' => $publication,
            'infoChap' => $chapter,
            'previousChap' => $previous,
            'nextChap' => $next,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/recit/comment/del/{id}', name: 'app_chapter_del_comment')]
    public function delComment(Request $request, EntityManagerInterface $em, PublicationChapterCommentRepository $pccRepo, $id): response
    {
        $comment = $pccRepo->find($id);
        if ($comment and $comment->getUser() == $this->getUser()) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Votre commentaire a bien été supprimé.');
            return $this->redirectToRoute('app_chapter_show', ['slugPub' => $comment->getChapter()->getPublication()->getSlug(), 'user' => $comment->getChapter()->getPublication()->getUser()->getUsername(), 'idChap' => $comment->getChapter()->getId(), 'slug' => $comment->getChapter()->getSlug()]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
}
