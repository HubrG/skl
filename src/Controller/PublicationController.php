<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Form\PublicationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicationController extends AbstractController
{
    #[Route('/story/add', name: 'app_publication_add')]
    public function index(Request $request, PublicationRepository $pubRepo, EntityManagerInterface $em): Response
    {
        // Si l'utilisateur est connecté...
        if ($this->getUser())
        {
            // GESTION DU BROUILLON
            // On récupère son dernier brouillon, s'il existe
            $brouillon = $pubRepo->findOneBy(["user" => $this->getUser(), "status" => 0]);
            if ($brouillon)
            {
                $form = $this->createForm(PublicationType::class, $brouillon);
            }
            else // Sinon, on crée une nouvelle ligne de brouillon
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
        }
        else
        {
            return $this->redirectToRoute("app_home");
        }
        // GESTION DU NEXT STEP
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // on modifie le statut du post => (1) ce n'est plus un brouillon
            $status =  $pubRepo->findOneBy(["user" => $this->getUser(), "status" => 0]);
            $status->setStatus(1);
            // on persiste et on envoi
            $em->persist($form->getData());
            $em->persist($status);
            $em->flush();
            //
            
            return $this->redirectToRoute("app_publication_edit_chapters", ["id" => $brouillon->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('publication/add_publication.html.twig', [
            'form' => $form,
            'pubId' => $brouillon->getId()
        ]);
    }
    #[Route('/story/edit/{id}/chapters', name: 'app_publication_edit_chapters')]
    public function step2(Request $request, $id = null, PublicationRepository $pubRepo, EntityManagerInterface $em): Response
    {
        $infoPublication = $pubRepo->findOneBy(["user" => $this->getUser(), "id" => $id]);
        return $this->render('publication/add_publication_2.html.twig', [
            'infoPublication' => $infoPublication
        ]);
    }
}
