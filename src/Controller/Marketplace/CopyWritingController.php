<?php

namespace App\Controller\Marketplace;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CopyWritingController extends AbstractController
{
    #[Route('/service/redaction', name: 'app_service_copywriting')]
    public function index(): Response
    {
        return $this->render('copy_writing/index.html.twig', [
            'controller_name' => 'CopyWritingController',
        ]);
    }
}
