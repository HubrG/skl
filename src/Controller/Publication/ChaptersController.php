<?php

namespace App\Controller\Publication;

use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChaptersController extends AbstractController
{
    // #[Route('/story/edit/{id}/chapters', name: 'app_publication_edit_chapters')]
    // public function step2(PublicationRepository $pubRepo, $id = null): Response
    // {
    //     $infoPublication = $pubRepo->findOneBy(["user" => $this->getUser(), "id" => $id]);
    //     return $this->render('publication/edit_chapter.html.twig', [
    //         'infoPublication' => $infoPublication
    //     ]);
    // }
}
