<?php

namespace App\Controller\User;

use DateTimeImmutable;
use App\Entity\UserFollow;
use App\Form\UserInfoType;
use App\Form\UserAccountType;
use App\Services\ImageService;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Form\UserChangePasswordType;
use App\Services\NotificationSystem;
use App\Repository\ChallengeRepository;
use App\Repository\ForumTopicRepository;
use App\Repository\UserFollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Form\UserChangePasswordGoogleType;
use App\Repository\ForumMessageRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationCommentRepository;
use App\Repository\PublicationBookmarkRepository;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\PublicationAnnotationRepository;
use App\Repository\PublicationChapterLikeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{


	public function __construct(
		private ImageService $uploadImage,
		private TokenStorageInterface $tokenStorage,
		private EmailVerifier $emailVerifier,
		private NotificationSystem $notificationSystem
	) {
	}
	#[Route('user/{username?}', requirements: ["username" => "[^/]+"], name: 'app_user')]
	public function index(UserRepository $userRepo, UserFollowRepository $ufRepo, PublicationRepository $pRepo, $username): Response
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
		if (!$userInfo) {
			return $this->redirectToRoute("app_home");
		}

		// On vérifie que l'utilisateur courant est ami ou non avec le membre dont on affiche la page
		if ($this->getUser()) {
			$user = $userRepo->find($this->getUser());
			$follow = $ufRepo->findOneBy(['fromUser' => $user, 'toUser' => $userInfo]);
			if ($follow) {
				$follow = true;
			} else {
				$follow = false;
			}
		} else {
			$follow = false;
		}
		$qb = $pRepo->createQueryBuilder("p")
			->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
			->where("p.status = 2")
			->andWhere("p.hideSearch = FALSE")
			->andWhere("p.user = :user")
			->setParameter("user", $userInfo);
		$pubInfo = $qb->getQuery()->getResult();
		return $this->render('user/user.html.twig', [
			'userInfo' => $userInfo,
			'pubInfo' => $pubInfo,
			'follow' => $follow
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
	#[Route('user/publications/show/{sort?}/{order?}/{username?}', name: 'app_user_show_publications')]
	public function showpublication(Request $request, PublicationRepository $pubRepo, UserRepository $userRepo, EntityManagerInterface $em, $sort = null, $order = null, $username = null): Response
	{
		if (!$this->getUser()) {
			return $this->redirectToRoute("app_home");
		}
		if ($username == null) {
			$user = $userRepo->findOneBy(["id" => $this->getUser()]);
		} elseif ($username != null && $this->isGranted("ROLE_ADMIN")) {
			$user = $userRepo->findOneBy(["username" => $username]);
		} else {
			$user = $userRepo->findOneBy(["id" => $this->getUser()]);
		}

		$order = $order ?? "desc";
		$sort = $sort ?? "created";

		$statusMin = null;
		if ($sort == 'pop' || $sort == 'views' || $sort == 'comments' || $sort == 'likes' || $sort == 'downloads' || $sort == 'category') {
			$statusMin = 1;
		}

		$qb = $this->creerConstructeurDeRequetePourUtilisateur($pubRepo, $user, $statusMin);

		if ($sort == "published_date" or $sort == "status" or $sort == "created" or $sort == "title") {
			$publications = $qb
				->orderBy("p." . $sort, $order)
				->addOrderBy("p.published_date", "desc")
				->getQuery()->getResult();
		} elseif ($sort == "views" || $sort == "likes") {
			$relation = "publicationChapterViews";
			if ($sort == "comments") {
				$relation = "publicationComments";
			} elseif ($sort == "likes") {
				$relation = "publicationChapterLikes";
			}
			$publications = $qb
				->leftJoin("p.publicationChapters", "pc")
				->leftJoin("pc." . $relation, "pcadd")
				->addSelect("COUNT(pcadd.id) AS HIDDEN add")
				->orderBy("add", $order)
				->getQuery()
				->getResult();
		} elseif ($sort == "downloads") {
			$publications = $qb
				->leftJoin("p.publicationDownloads", "pc")
				->addSelect("COUNT(pc.id) AS HIDDEN add")
				->orderBy("add", $order)
				->getQuery()
				->getResult();
		} elseif ($sort == "comments") {
			$publications = $qb
				->leftJoin("p.publicationComments", "pc")
				->addSelect("SUM(CASE WHEN pc.publication = p.id THEN 1 ELSE 0 END) AS HIDDEN add")
				->orderBy("add", $order)
				->getQuery()
				->getResult();
		} elseif ($sort == "pop") {
			$publications = $qb
				->leftJoin("p.publicationPopularities", "pp")
				->addSelect("SUM(pp.popularity) AS HIDDEN popp")
				->orderBy("p.pop", $order)
				->groupBy("p.id")
				->getQuery()
				->getResult();
		} elseif ($sort == "category") {
			$publications = $qb
				->orderBy("p.category", $order)
				->getQuery()
				->getResult();
		} elseif ($sort == "chapters") {
			$publications = $qb
				->leftJoin("p.publicationChapters", "pc")
				->addSelect("SUM(CASE WHEN pc.status = 2 THEN 1 ELSE 0 END) AS HIDDEN add")
				->orderBy("add", $order)
				->getQuery()
				->getResult();
		}

		if ($sort != "published_date" && $sort != "status" && $sort != "created" && $sort != "title" and $sort != "chapters") {
			$qb2 = $pubRepo
				->createQueryBuilder("p")
				->where("p.status = 1 and p.user = :user")
				->setParameter('user', $user->getId())
				->orderBy("p.created", "asc")
				->addOrderBy("p.published_date", "desc");

			$publicationsOffline = $qb2->getQuery()->getResult();
			$publications = array_merge($publications, $publicationsOffline);
		}


		return $this->render('user/show_publication.html.twig', [
			'publication' => $publications,
			'userInfo' => $this->getUser()
		]);
	}

	private function creerConstructeurDeRequetePourUtilisateur(PublicationRepository $pubRepo, $user, $statusMin)
	{
		$qb = $pubRepo
			->createQueryBuilder("p")
			->where("p.user = :user")
			->setParameter('user', $user->getId())
			->groupBy("p.id");

		if ($statusMin !== null) {
			$qb->andWhere("p.status > :statusMin")
				->setParameter('statusMin', $statusMin);
		}

		return $qb;
	}



	#[Route('update/user/update_picture', name: 'app_user_update_picture')]
	public function update_picture(Request $request, UserRepository $userRepo): Response
	{
		if ($request->files->get("pp")) {
			$dtPp = $request->files->get("pp");
			return $this->uploadImage->UploadImage($dtPp, "profil_picture", $userRepo->find($this->getUser())->getId(), 500, 500);
		} elseif ($request->files->get("pbg")) {
			$dtPbg = $request->files->get("pbg");
			return $this->uploadImage->UploadImage($dtPbg, "profil_background", $userRepo->find($this->getUser())->getId(), 1680, 600);
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
						->from(new Address('admin@scrilab.com', 'Scrilab'))
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
						->from(new Address('admin@scrilab.com', 'Scrilab'))
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
	public function collection(Request $request, PublicationChapterLikeRepository $pclRepo, PublicationAnnotationRepository $paRepo, PublicationBookmarkRepository $pbmRepo, EntityManagerInterface $em): Response
	{


		if (!$this->getUser()) {
			return $this->redirectToRoute("app_home");
		}

		$varDeleteType = $request->query->get('delete');
		$varId = $request->query->get('id');
		if ($varDeleteType == "favPub") {
			$pbm = $pbmRepo->find($varId);
			$em->remove($pbm);
			$em->flush();
			$this->addFlash('success', 'Le récit a bien été supprimée de votre collection');
		}
		if ($varDeleteType == "mark") {
			$pa = $paRepo->find($varId);
			if ($pa->getMode() == 0) {
				$em->remove($pa);
				$em->flush();
				$this->addFlash('success', 'Ce surlignage a bien supprimé de votre collection');
			}
		}
		if ($varDeleteType == "bmChap") {
			$pbm = $pbmRepo->find($varId);
			$em->remove($pbm);
			$em->flush();
			$this->addFlash('success', 'Le chapitre n\'est plus marqué');
		}
		if ($varDeleteType == "likeChap") {
			$pbm = $pclRepo->find($varId);
			$em->remove($pbm);
			$em->flush();
			$this->addFlash('success', 'Le chapitre a été retiré de vos « J\'aime » ');
		}

		return $this->render('user/my_collection.html.twig', [
			'controller_name' => 'MyCollectionController',
			'userInfo' => $this->getUser()

		]);
	}
	#[Route('/delete-account', name: 'app_user_delete_account')]
	public function deleteAccount(UserRepository $userRepo, ResetPasswordRequestRepository $rprRepo, EntityManagerInterface $em, PublicationCommentRepository $pcRepo): Response
	{
		// On supprime le compte de l'utilisateur connecté
		$user = $userRepo->find($this->getUser());
		// On supprime les enregistrement ResetPassword si existants pour cet utilisateur
		$rpr = $rprRepo->findBy(['user' => $user]);
		if ($rpr) {
			foreach ($rpr as $r) {
				$em->remove($r);
			}
		}
		// On déconnecte l'utilisateur
		$this->tokenStorage->setToken(null);
		$em->remove($user);
		$em->flush();

		$this->addFlash('success', "&nbsp;&nbsp;Votre compte a bien été supprimé. N'hésitez pas à nous rejoindre à nouveau !");
		return $this->redirectToRoute("app_logout");
	}
	#[Route('/follow/user', name: 'app_user_follow', methods: ['POST'])]
	public function followUser(Request $request, UserFollowRepository $ufRepo, UserRepository $uRepo, EntityManagerInterface $em): Response
	{
		$userAdded = $request->get("user");
		$userAdded = $uRepo->find($userAdded);
		//
		$user = $this->getUser();
		$follow = $ufRepo->findOneBy(['fromUser' => $user, 'toUser' => $userAdded]);
		if ($follow) {
			$em->remove($follow);
			$em->flush();
			return $this->json([
				'code' => 200,
				'message' => 'Vous ne suivez plus ' . $userAdded->getNickname(),
				'follow' => false
			], 201);
		} else {
			$follow = new UserFollow();
			$follow->setFromUser($user);
			$follow->setToUser($userAdded);
			$follow->setAddedAt(new DateTimeImmutable());
			$em->persist($follow);
			$em->flush();
			$this->notificationSystem->addNotification(18, $follow->getToUser(), $this->getUser(), $follow);
			return $this->json([
				'code' => 201,
				'message' => 'Vous suivez ' . $userAdded->getNickname(),
				'follow' => true
			], 200);
		}
	}
	#[Route('/user_nav/forum/{username}', name: 'app_user_nav_forum')]
	public function userNavForum(ForumTopicRepository $ftRepo, ForumMessageRepository $fmRepo, UserRepository $uRepo, $username = null): Response
	{
		$userInfo = $uRepo->findOneBy(["username" => $username]);
		// On récupère tous les topics du forum créés par l'utilisateur
		$qb = $ftRepo->createQueryBuilder('t'); // 't' est un alias pour 'topic'
		$qb->join('t.forumMessages', 'm') // 'm' est un alias pour 'message'
			->where('t.user = :user')
			->setParameter('user', $userInfo)
			->orderBy('t.permanent', 'DESC')
			->addOrderBy('m.published_at', 'DESC')
			->addOrderBy('t.createdAt', 'DESC'); // Tri par date de publication du dernier message

		$topics = $qb->getQuery()->getResult();
		// On récupère tous les messages du forum créés par l'utilisateur
		$messages = $fmRepo->findBy(["user" => $userInfo], ["published_at" => "DESC"]);

		// * On récupère le nombre de derniers messages depuis la dernière visite de l'utilisateur
		// Récupérer l'utilisateur connecté
		$user = $this->getUser();

		if ($user) {
			// Récupérer le nombre de messages non lus pour chaque topic
			$unreadMessageCounts = [];
			foreach ($topics as $topic) {
				$unreadMessageCounts[$topic->getId()] = $fmRepo->getUnreadMessageCountForUser($user, $topic);
			}
		} else {

			$unreadMessageCounts = 0;
		}

		return $this->render('user/user-nav/forum.html.twig', [
			'userInfo' => $userInfo,
			'topics' => $topics,
			'messages' => $messages,
			'unreadMessageCounts' => $unreadMessageCounts
		]);
	}
	#[Route('/user_nav/challenge/{username}', name: 'app_user_nav_challenge')]
	public function userNavChallenge(ChallengeRepository $cRepo, UserRepository $uRepo, $username = null): Response
	{
		$userInfo = $uRepo->findOneBy(["username" => $username]);
		// On récupère tous les topics du forum créés par l'utilisateur
		$challenges = $cRepo->findBy(
			["user" => $userInfo],

			['createdAt' => 'DESC']
		);

		return $this->render('user/user-nav/challenge.html.twig', [
			'userInfo' => $userInfo,
			'challenges' => $challenges
		]);
	}
	#[Route('/user_nav/contact/{username}', name: 'app_user_nav_contact')]
	public function userNavContact(UserFollowRepository $ufRepo, UserRepository $uRepo, $username = null): Response
	{
		$userInfo = $uRepo->findOneBy(["username" => $username]);
		$following = $ufRepo->findBy(["fromUser" => $userInfo], ["addedAt" => "DESC"]);
		$followed = $ufRepo->findBy(["toUser" => $userInfo], ["addedAt" => "DESC"]);

		return $this->render('user/user-nav/contact.html.twig', [
			'userInfo' => $userInfo,
			'following' => $following,
			'followed' => $followed
		]);
	}
}
