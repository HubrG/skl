<?php

namespace App\Controller\User;

use App\Form\UserInfoType;
use App\Form\UserAccountType;
use App\Services\ImageService;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Form\UserChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Form\UserChangePasswordGoogleType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationCommentRepository;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
	private $tokenStorage;

	private $uploadImage;

	private EmailVerifier $emailVerifier;

	public function __construct(ImageService $uploadImage, TokenStorageInterface $tokenStorage, EmailVerifier $emailVerifier)
	{
		$this->uploadImage = $uploadImage;
		$this->tokenStorage = $tokenStorage;
		$this->emailVerifier = $emailVerifier;
	}
	#[Route('user/{username?}', requirements: ["username" => "[^/]+"], name: 'app_user')]
	public function index(UserRepository $userRepo, PublicationRepository $pRepo, $username): Response
	{
		/// Conditions d'affichage
		// Si le username n'est pas renseigné et que l'utilisateur est connecté, alors on affiche la page du membre connecté
		if ($username == null && $this->getUser()) {
			$userInfo = $userRepo->find($this->getUser());
		}
		// Si le username n'est pas renseigné et que l'utilisateur n'est pas connecté, alors on le redirige
		elseif ($username == null && !$this->getUser()) {
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
			'editUserForm' => $form,
			'userInfo' => $this->getUser()

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
			'publication' => $publications,
			'userInfo' => $this->getUser()
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
	public function account(Request $request, EntityManagerInterface $em, UserRepository $userRepo, UserPasswordHasherInterface $userPasswordHasher, $success = null): Response
	{
		if (!$this->getUser()) {
			return $this->redirectToRoute("app_home");
		}
		$user = $userRepo->find($this->getUser());
		$userEmail = $user->getEmail();
		$form = $this->createForm(UserAccountType::class, $user);
		if ($user->getGoogleId() && $user->getPassword() == "") {
			$pwForm = $this->createForm(UserChangePasswordGoogleType::class, $user);
		} else {
			$pwForm = $this->createForm(UserChangePasswordType::class, $user);
		}
		//
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			if ($user->getGoogleId() && $user->getPassword() == "" && $userEmail != $form->get("email")->getData()) {
				$this->addFlash('error', 'Vous ne pouvez pas modifier votre adresse e-mail car vous êtes connecté avec Google et que vous n\'avez pas de mot de passe');
				$url = $this->generateUrl('app_user_account');
				return new Response(<<<HTML
				<!DOCTYPE html>
				<html>
					<head>
						<title>Redirection</title>
						<script>
							location.top.location.href = "$url";
						</script>
					</head>
					<body>
					</body>
				</html>
			HTML);
			} elseif ($user->getGoogleId() && $user->getPassword() != "" && $userEmail != $form->get("email")->getData()) {
				$user->setGoogleId(null);
				$user->setIsVerified(false);
				$this->emailVerifier->sendEmailConfirmation(
					'app_verify_email',
					$user,
					(new TemplatedEmail())
						->from(new Address('contact@scrilab.fr', 'Scrilab'))
						->to($user->getEmail())
						->subject('Confirmez votre adresse adresse email.')
						->htmlTemplate('emails/valid_email.html.twig')
				);
			} elseif (!$user->getGoogleId() && $user->getPassword() != "" && $userEmail != $form->get("email")->getData()) {
				$user->setIsVerified(false);
				$this->emailVerifier->sendEmailConfirmation(
					'app_verify_email',
					$user,
					(new TemplatedEmail())
						->from(new Address('contact@scrilab.fr', 'Scrilab'))
						->to($user->getEmail())
						->subject('Confirmez votre adresse adresse email.')
						->htmlTemplate('emails/valid_email.html.twig')
				);
			}
			$em->persist($user);
			$em->flush();
			$this->addFlash('success', 'Vos informations ont bien été modifées');
			$url = $this->generateUrl('app_user_account');
			return new Response(<<<HTML
				<!DOCTYPE html>
				<html>
					<head>
						<title>Redirection</title>
						<script>
							location.top.location.href = "$url";
						</script>
					</head>
					<body>
					</body>
				</html>
			HTML);
		}
		// ! Password
		$pwForm->handleRequest($request);
		if ($pwForm->isSubmitted() && $pwForm->isValid()) {
			if ($user->getGoogleId() && $user->getPassword() == "") {
				$newEncodedPassword = $userPasswordHasher->hashPassword($user, $user->getPlainPassword());
				$this->addFlash('success', 'Votre mot de passe a bien été crée.');
			} else {
				$newEncodedPassword = $userPasswordHasher->hashPassword($user, $user->getPlainPassword());
				$this->addFlash('success', 'Votre mot de passe a bien été modifié.');
			}
			$user->setPassword($newEncodedPassword);
			$em->persist($user);
			$em->flush();
			$url = $this->generateUrl('app_user_account');
			return new Response(<<<HTML
				<!DOCTYPE html>
				<html>
					<head>
						<title>Redirection</title>
						<script>
							window.top.location.href = "$url";
						</script>
					</head>
					<body>
					</body>
				</html>
			HTML);
		}
		return $this->render('user/account.html.twig', [
			'form' => $form,
			'passwordForm' => $pwForm,
			'userInfo' => $this->getUser()
		]);
	}
	#[Route('/collection', name: 'app_user_collection')]
	public function collection(): Response
	{
		if (!$this->getUser()) {
			return $this->redirectToRoute("app_home");
		}

		return $this->render('user/my_collection.html.twig', [
			'controller_name' => 'MyCollectionController',
			'userInfo' => $this->getUser()

		]);
	}
	#[Route('/delete-account', name: 'app_user_delete_account')]
	public function deleteAccount(UserRepository $userRepo, EntityManagerInterface $em, PublicationCommentRepository $pcRepo): Response
	{
		// On supprime le compte de l'utilisateur connecté
		$user = $userRepo->find($this->getUser());
		// On déconnecte l'utilisateur
		$this->tokenStorage->setToken(null);
		$em->remove($user);
		$em->flush();

		$this->addFlash('success', "&nbsp;&nbsp;Votre compte a bien été supprimé. N'hésitez pas à nous rejoindre à nouveau !");
		return $this->redirectToRoute("app_logout");
	}
}
