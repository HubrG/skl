<?php

namespace App\Controller;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Publication;
use Psr\Log\LoggerInterface;
use App\Entity\PublicationKeyword;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationKeywordRepository;
use App\Repository\PublicationCategoryRepository;
use Proxies\__CG__\App\Entity\PublicationCategory;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
