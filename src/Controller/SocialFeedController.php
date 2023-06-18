<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SocialFeedController extends AbstractController
{
    #[Route('/fil_info', name: 'app_social_feed')]
    public function index(): Response
    {
        return $this->render('social_feed/index.html.twig', [
            'controller_name' => 'SocialFeedController',
        ]);
    }
}
