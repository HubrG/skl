<?php

namespace App\Controller\User;

use App\Form\UserInfoType;
use Cloudinary\Cloudinary;
use App\Services\ImageService;
use App\Repository\UserRepository;
use Cloudinary\Transformation\Resize;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UserController extends AbstractController
{

	private $uploadImage;

	public function __construct(ImageService $uploadImage)
	{
		$this->uploadImage = $uploadImage;
	}
	#[Route('user/{username}', requirements: ["username" => "[^/]+"], name: 'app_user')]
	public function index(UserRepository $userRepo, PublicationRepository $pRepo, $username = "me"): Response
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

		$qb = $pRepo->createQueryBuilder("p")
			->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
			->where("p.status = 2")
			->andWhere("p.user = :user")
			->setParameter("user", $userInfo);
		$pubInfo = $qb->getQuery()->getResult();
		return $this->render('user/user.html.twig', [
			'userInfo' => $userInfo,
			'pubInfo' => $pubInfo
		]);
	}
	#[Route('user/edit/{username}', name: 'app_user_edit')]
	public function edit(Request $request, UserRepository $userRepo, EntityManagerInterface $em, $id = null): Response
	{
		$user = $userRepo->find($this->getUser());
		if (!$this->getUser() && $user != $this->getUser()) {
			return $this->redirectToRoute("app_home");
		}
		/// Création du formulaire
		$form = $this->createForm(UserInfoType::class, $this->getUser()); // $user = utilisateur loggué (UserInterface)
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// On envoi le formulaire dans la base de données

			$user->setNickname($form->get("nickname")->getData());
			$user->setAbout($form->get("about")->getData());
			$em->persist($user);
			$em->flush();
			// flash
			$this->addFlash('successTitle', 'Modification enregistrées !');
			$this->addFlash('successMessage', 'Les modifications apportées sur votre page de profil sont désormais visibles de tous.');
			// return

			return $this->redirectToRoute("app_user", ["username" => $user->getUsername()], Response::HTTP_SEE_OTHER);
		}
		return $this->render('user/edit.html.twig', [
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
		return $this->render('user/show_publication.html.twig', [
			'publication' => $publications
		]);
	}
	#[Route('update/user/update_picture', name: 'app_user_update_picture')]
	public function update_picture(Request $request, UserRepository $userRepo): Response
	{
		if ($request->files->get("pp")) {
			$dtPp = $request->files->get("pp");
			return $this->uploadImage->UploadImage($dtPp, "profil_picture", $userRepo->find($this->getUser())->getId(), 500, 500);
		} elseif ($request->files->get("pbg")) {
			$dtPbg = $request->files->get("pbg");
			return $this->uploadImage->UploadImage($dtPbg, "profil_background", $userRepo->find($this->getUser())->getId(), 600, 300);
		} else {
			return $this->redirectToRoute("app_home");
		}
	}
}
