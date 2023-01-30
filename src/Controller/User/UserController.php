<?php

namespace App\Controller\User;

use App\Form\UserInfoType;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('user/{username}', requirements: ["username" => "[^/]+"], name: 'app_user')]
    public function index(UserRepository $userRepo, $username = "me"): Response
    {
        /// Conditions d'affichage
        // Si le username n'est pas renseigné et que l'utilisateur est connecté, alors on affiche la page du membre connecté
        if ($username == "me" && $this->getUser()) {
            $userInfo = $userRepo->findOneBy(["email" => $this->getUser()]);
        }
        // Si le username n'est pas renseigné et que l'utilisateur n'est pas connecté, alors on le redirige

        elseif ($username == "me" && !$this->getUser()) {
            return $this->redirectToRoute("app_home");
        }
        // Si le username est renseigné alors on affiche la page du membre du username

        else {
            $userInfo = $userRepo->findOneBy(["username" => $username]);
        }
        return $this->renderForm('user/user.html.twig', [
            'userInfo' => $userInfo,
        ]);
    }
    #[Route('user/edit/{id}', name: 'app_user_edit')]
    public function edit(Request $request, UserRepository $userRepo, EntityManagerInterface $em, $id = null): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute("app_home");
        }
        /// Création du formulaire
        $form = $this->createForm(UserInfoType::class, $this->getUser()); // $user = utilisateur loggué (UserInterface)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userUp = $userRepo->find($this->getUser());
            $uploadedFile = $form['profil_picture']->getData();
            if ($uploadedFile) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/images/uploads/profil_pictures/' . $userUp->getId();
                $newFilename = $userUp->getId() . '.' . $uploadedFile->guessExtension();
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $userUp->setProfilPicture($newFilename);
            }
            $em->persist($userUp);
            $em->flush();
            // flash
            $this->addFlash('successTitle', 'Modification enregistrées !');
            $this->addFlash('successMessage', 'Les modifications apportées sur votre page de profil sont désormais visibles de tous.');
            // return
            return $this->redirectToRoute("app_user", ["username" => $userUp->getUsername()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('user/edit.html.twig', [
            'editUserForm' => $form
        ]);
    }
    #[Route('user/publications/show', name: 'app_user_show_publications')]
    public function showpublication(Request $request, PublicationRepository $pubRepo, UserRepository $user, EntityManagerInterface $em): Response
    {

        $user = $user->findOneBy(["id" => $this->getUser()]);
        $user = $user->getId();
        $publications = $pubRepo->createQueryBuilder("u")->where("u.status > 0 and u.user = " . $user)->getQuery()->getResult();
        foreach ($publications as $publication) {
        }
        return $this->renderForm('user/show_publication.html.twig', [
            'publication' => $publications
        ]);
    }
}
