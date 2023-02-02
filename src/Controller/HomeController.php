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

    private $t;

    public function __construct()
    {
        $this->t = new Pdf();
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request, PublicationRepository $pRepo, SluggerInterface $slugger,  EntityManagerInterface $em): Response
    {

        return $this->redirectToRoute('app_publication_show_all_category');



        // return  $this->render('home/home.html.twig', [
        //     'controller_name' => "d",
        //     "test" =>  "d"
        // ]);
    }
}
