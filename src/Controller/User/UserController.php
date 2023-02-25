<?php

namespace App\Controller\User;

use App\Form\UserInfoType;
use Cloudinary\Cloudinary;
use App\Form\UserAccountType;
use App\Services\ImageService;
use App\Repository\UserRepository;
use App\Form\UserChangePasswordType;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
			$this->addFlash('success', 'Modification enregistrées !');
			// return
			return $this->redirectToRoute("app_user", ["username" => $user->getUsername()], Response::HTTP_SEE_OTHER);
		}
		return $this->render('user/edit.html.twig', [
			'editUserForm' => $form
		]);
	}
	#[Route('user/publications/show/{sort?}/{order?}', name: 'app_user_show_publications')]
	public function showpublication(Request $request, PublicationRepository $pubRepo, UserRepository $user, EntityManagerInterface $em, $sort = null, $order = null): Response
	{
		if (!$this->getUser()) {
			return $this->redirectToRoute("app_home");
		}
		$user = $user->findOneBy(["id" => $this->getUser()]);
		$user = $user->getId();
		//
		$order = $order ?? "desc";
		$sort = $sort ?? "created";
		//
		if ($sort == "published_date" or $sort == "status" or $sort == "created" or $sort == "title") {
			$publications = $pubRepo
				->createQueryBuilder("p")
				->where("p.status > 0 and p.user = " . $user)
				->groupBy("p.id")
				->orderBy("p." . $sort, $order)
				->addOrderBy("p.published_date", "desc")
				->getQuery()->getResult();
		} elseif ($sort == "pop") {
			$publications = $pubRepo
				->createQueryBuilder("p")
				->where("p.status > 1 and p.user = " . $user)
				->groupBy("p.id")
				->orderBy("p." . $sort, $order)
				->addOrderBy("p.published_date", "desc")
				->getQuery()->getResult();
		} elseif ($sort == "views") {
			$publications = $pubRepo
				->createQueryBuilder("p")
				->leftJoin("p.publicationChapters", "pc")
				->leftJoin("pc.publicationChapterViews", "pcadd")
				->addSelect("COUNT(pcadd.id) AS HIDDEN add")
				->where("p.status > 1 and p.user = " . $user)
				->groupBy("p.id")
				->orderBy("add", $order)
				->getQuery()
				->getResult();
		} elseif ($sort == "comments") {
			$publications = $pubRepo
				->createQueryBuilder("p")
				->leftJoin("p.publicationChapters", "pc")
				->leftJoin("pc.publicationComments", "pcadd")
				->addSelect("COUNT(pcadd.id) AS HIDDEN add")
				->where("p.status > 1 and p.user = " . $user)
				->groupBy("p.id")
				->orderBy("add", $order)
				->getQuery()
				->getResult();
		} elseif ($sort == "likes") {
			$publications = $pubRepo
				->createQueryBuilder("p")
				->leftJoin("p.publicationChapters", "pc")
				->leftJoin("pc.publicationChapterLikes", "pcadd")
				->addSelect("COUNT(pcadd.id) AS HIDDEN add")
				->where("p.status > 1 and p.user = " . $user)
				->groupBy("p.id")
				->orderBy("add", $order)
				->getQuery()
				->getResult();
		} elseif ($sort == "downloads") {
			$publications = $pubRepo
				->createQueryBuilder("p")
				->leftJoin("p.publicationDownloads", "pc")
				->addSelect("COUNT(pc.id) AS HIDDEN add")
				->where("p.status > 1 and p.user = " . $user)
				->groupBy("p.id")
				->orderBy("add", $order)
				->getQuery()
				->getResult();
		} elseif ($sort == "category") {
			$publications = $pubRepo
				->createQueryBuilder("p")
				->where("p.status > 1 and p.user = " . $user)
				->groupBy("p.id")
				->orderBy("p.category", $order)
				->getQuery()
				->getResult();
		} elseif ($sort == "chapters") {
			$publications = $pubRepo
				->createQueryBuilder("p")
				->leftJoin("p.publicationChapters", "pc")
				->addSelect("COUNT(pc.id) AS HIDDEN add")
				->where("p.status > 1 and p.user = " . $user)
				->groupBy("p.id")
				->orderBy("add", $order)
				->getQuery()
				->getResult();
		}
		if ($sort != "published_date" && $sort != "status" && $sort != "created" && $sort != "title") {
			$publicationsOffline = $pubRepo
				->createQueryBuilder("p")
				->where("p.status = 1 and p.user = " . $user)
				->groupBy("p.id")
				->orderBy("p.created", "asc")
				->addOrderBy("p.published_date", "desc")
				->getQuery()->getResult();
			$publications = array_merge($publications, $publicationsOffline);
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
			return $this->uploadImage->UploadImage($dtPbg, "profil_background", $userRepo->find($this->getUser())->getId(), 1024, 500);
		} else {
			return $this->redirectToRoute("app_home");
		}
	}
	#[Route('update/user/account', name: 'app_user_account')]
	public function account(Request $request, EntityManagerInterface $em, UserRepository $userRepo,  UserPasswordHasherInterface $userPasswordHasher, $success = null): Response
	{
		if (!$this->getUser()) {
			return $this->redirectToRoute("app_home");
		}
		$user = $userRepo->findOneBy(["id" => $this->getUser()]);
		$form = $this->createForm(UserAccountType::class, $user);
		$pwForm = $this->createForm(UserChangePasswordType::class, $user);
		//
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->persist($user);
			$em->flush();
			$this->addFlash('success', 'Vos informations ont bien été modifées');
			return $this->redirectToRoute("app_user_account", [], Response::HTTP_SEE_OTHER);
		}

		$pwForm->handleRequest($request);
		if ($pwForm->isSubmitted() && $pwForm->isValid()) {
			$newEncodedPassword = $userPasswordHasher->hashPassword($user, $user->getPlainPassword());
			$user->setPassword($newEncodedPassword);
			$em->persist($user);
			$em->flush();
			$this->addFlash('success', 'Votre mot de passe a bien été modifié.');
			return $this->redirectToRoute("app_user_account", [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('user/account.html.twig', [
			'form' => $form,
			'passwordForm' => $pwForm
		]);
	}
}
