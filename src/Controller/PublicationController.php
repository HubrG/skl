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
    #[Route('/publication', name: 'app_publication')]
    public function index(Request $request, PublicationRepository $pubRepo, UserInterface $logUser, EntityManagerInterface $em): Response
    {
        // Si l'utilisateur est connecté
        if ($this->getUser())
        {
            // On récupère son dernier brouillon, s'il existe
            $brouillon = $pubRepo->findOneBy(["user" => $logUser, "status" => 0]);
            if ($brouillon) {
                $form = $this->createForm(PublicationType::class, $brouillon);
            }
            else // Sinon, on crée une nouvelle ligne de brouillon
            {
                $publication = new Publication();
                $coucou = $publication->setUser($logUser);
                $coucou = $publication->setStatus(0);
                $coucou = $publication->setType(0);
                $coucou = $publication->setMature(0);
                $em->persist($coucou);
                $em->flush();
            }
        }

        return $this->renderForm('publication/publication.html.twig', [
            'form' => $form,
        ]);
    }
}
