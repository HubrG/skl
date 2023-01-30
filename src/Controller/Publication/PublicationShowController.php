<?php

namespace App\Controller\Publication;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationCategoryRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicationShowController extends AbstractController
{
    #[Route('/stories/{slug}', name: 'app_publication_show_all')]
    public function show_all(PublicationCategoryRepository $pcRepo, PublicationRepository $pRepo, PublicationChapterRepository $pchRepo, $slug = null): Response
    {
        $pcRepo = $pcRepo->findOneBy(["slug" => $slug]);
        if ($pcRepo) {
            $publications = $pRepo->findBy(["category" => $pcRepo->getId()]);
            return $this->render('publication/show_all.html.twig', [
                'pubShow' => $publications,
            ]);
        } else {
            return $this->redirectToRoute("app_home", [], Response::HTTP_SEE_OTHER);
        }
    }
    #[Route('/story/{id}/{slug}', name: 'app_publication_show_one')]
    public function show_one($pubId = null): Response
    {
        return $this->renderForm('publication/show_one.html.twig', [
            'pubShow' => "ok"
        ]);
    }
}
