<?php

namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    public function index(Request $request, PublicationRepository $pRepo, SluggerInterface $slugger,  EntityManagerInterface $em): Response
    {
        // on récupère toutes les publications afin de leur ajouter un slug
        // $publications = $pRepo->findAll();
        // foreach ($publications as $publication) {
        //     $couco = $publication->getTitle();
        //     $coucou = $slugger->slug("'" . $couco . '"');
        //     $publication->setSlug($coucou);
        //     $em->persist($publication);
        // }
        // $em->flush();


        return $this->render('home/home.html.twig', [
            'controller_name' => "d"
        ]);
    }
}
