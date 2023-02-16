<?php

namespace App\Controller\Admin;

use App\Entity\PublicationCategory;
use App\Form\PublicationCategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationCategoryRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_admin_category')]
    public function index(Request $request, SluggerInterface $slugger, EntityManagerInterface $em, PublicationCategoryRepository $pubCatRepo): Response
    {
        // AJOUT D'UNE CATÉGORIE
        // création du formulaire d'édition
        $form = $this->createForm(PublicationCategoryType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // on ajoute au formulaire la donnée de slug
            $form = $form->getData()->setSlug($slugger->slug(strtolower($form->getData()->getName())));
            $em->persist($form);
            $em->flush();
            return $this->redirectToRoute("app_admin_category", [], Response::HTTP_SEE_OTHER);
        }
        // récupération des catégories
        $pubCatRepo = $pubCatRepo->findBy([], ["name" => "ASC"]);
        return $this->renderForm('admin/category/category.html.twig', [
            'formPublicationCat' => $form,
            'categories' => $pubCatRepo
        ]);
    }
    #[Route('/admin/category/delete/{id}', name: 'app_admin_category_delete')]
    public function del(PublicationCategoryRepository $pubCatRepo, EntityManagerInterface $em, $id = null): Response
    {
        $pubCatRepo = $pubCatRepo->find($id);
        $em->remove($pubCatRepo);
        $em->flush();
        return $this->redirectToRoute("app_admin_category", [], Response::HTTP_SEE_OTHER);
    }
}
