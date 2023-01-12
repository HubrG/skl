<?php

namespace App\Controller;

use App\Form\UserInfoType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/user/{username}', name: 'app_user')]
    public function index(UserRepository $userRepo, Request $request, UserInterface $logUser = null, EntityManagerInterface $em, $username = "me"): Response
    {
        /// Conditions d'affichage
        // Si le username n'est pas renseigné et que l'utilisateur est connecté, alors on affiche la page du membre connecté
        if ($username == "me" && $this->getUser())
        {
            $userInfo = $userRepo->findOneBy(["email" => $logUser->getEmail()]);
        }
        // Si le username n'est pas renseigné et que l'utilisateur n'est pas connecté, alors on le redirige
        elseif ($username == "me" && !$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        // Si le username est renseigné alors on affiche la page du membre du username
        else
        {
            $userInfo = $userRepo->findOneBy(["username" => $username]);
        }
        return $this->renderForm('user/user.html.twig', [
            'userInfo' => $userInfo,
        ]);
    }
    #[Route('user/edit/{id}', name: 'app_user_edit')]
    public function edit(Request $request, UserRepository $userRepo, UserInterface $logUser = null, $id, EntityManagerInterface $em): Response
    {
        if (!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        /// Création du formulaire
        $form = $this->createForm(UserInfoType::class, $logUser); // $user = utilisateur loggué (UserInterface)
        $form->handleRequest($request);
        $pp = $logUser->getProfilPicture();
    if ($form->isSubmitted() && $form->isValid())
        {
            $userUp = $userRepo->find($logUser->getId());
            $uploadedFile = $form['profil_picture']->getData();
            if ($uploadedFile) {
                $destination = $this->getParameter('kernel.project_dir').'/public/images/uploads/profil_pictures/'.$logUser->getId();
                $newFilename = $logUser->getId().'.'.$uploadedFile->guessExtension();
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $userUp->setProfilPicture($newFilename);
            }
            $em->persist($userUp);
            $em->flush();
            // flash
            $this->addFlash('success', '');
            $this->addFlash('successTitle', 'Modification enregistrées !');
            $this->addFlash('successMessage', 'Les modifications apportées sur votre page de profil sont désormais visibles de tous.');
            // return
            return $this->redirectToRoute("app_user", ["username" => $logUser->getUsername()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('user/edit.html.twig', [
            'editUserForm' => $form
        ]);
    }
}
