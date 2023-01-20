<?php

namespace App\Controller;

use cebe\markdown\Markdown;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index($age, Request $request, LoggerInterface $logger): Response
    {
        return $this->render('home/home.html.twig', [
            'controller_name' => "dd"
        ]);
    }
}
