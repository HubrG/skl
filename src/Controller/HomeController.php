<?php

namespace App\Controller;

use Symfony\Component\Mime\Email;
use App\Entity\PublicationComment;
use App\Form\PublicationCommentType;
use Symfony\Component\Mailer\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationCommentRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    public function index(Request $request, MailerInterface $mailer, PublicationRepository $pRepo, SluggerInterface $slugger,  EntityManagerInterface $em): Response
    {


        // return $this->redirectToRoute('app_publication_show_all_category');


        return  $this->render('home/home.html.twig', [
            'controller_name' => "d",
            "test" =>  "d"
        ]);
    }
    #[Route('/test/{nbrShowCom?}', name: 'app_test')]
    public function test(Request $request, PublicationCommentRepository $pcomRepo, PublicationRepository $pRepo, SluggerInterface $slugger,  EntityManagerInterface $em, $nbrShowCom = 10): Response
    {
        // $nbrShowCom = $nbrShowCom ?? 10;
        // On cherche dans le pcomRepo avec une limite de 10
        // $pcom = $pcomRepo->findBy(["chapter" => null], ["id" => "DESC"], $nbrShowCom);
        // $pcomCount = count($pcomRepo->findBy(["chapter" => null]));
        // $prepo = $pRepo->find(4);
        return $this->redirectToRoute('app_publication_show_all_category');
        // $form = $this->createForm(PublicationCommentType::class);
        // $form->handleRequest($request);
        // if ($form->isSubmitted() && $form->isValid()) {
        //     $pcom = $form->getData();
        //     $pcom->setUser($this->getUser());
        //     $pcom->setPublication($prepo);
        //     $pcom->setPublishedAt(new \DateTimeImmutable());
        //     $em->persist($pcom);
        //     $em->flush();
        //     return $this->redirectToRoute('app_test');
        // }
        // return  $this->render('home/test.html.twig', [
        //     'pCom' => $pcom,
        //     'form' => $form->createView(),
        //     'nbrShowCom' => $nbrShowCom,
        //     "nbrCom" => $pcomCount,
        // ]);
    }
}
