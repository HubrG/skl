<?php

namespace App\Controller\User;

use App\Form\UserInfoType;
use Cloudinary\Cloudinary;
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
		return $this->render('user/user.html.twig', [
			'userInfo' => $userInfo,
		]);
	}
	#[Route('user/edit/{username}', name: 'app_user_edit')]
	public function edit(Request $request, UserRepository $userRepo, EntityManagerInterface $em, $id = null): Response
	{
		if (!$this->getUser()) {
			return $this->redirectToRoute("app_home");
		}
		/// Création du formulaire
		$form = $this->createForm(UserInfoType::class, $this->getUser()); // $user = utilisateur loggué (UserInterface)
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// On envoi le formulaire dans la base de données

			$user = $userRepo->find($this->getUser());
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
	#[Route('update/user/update_pp', name: 'app_user_update_pp')]
	public function update_pp(MimeTypesInterface $mimeTypes, Request $request, UserRepository $user, EntityManagerInterface $em): Response
	{
		$dtPp = $request->files->get("pp");
		//! Le ficheir est-il une image ?
		$file = new File($dtPp);
		$mimeType = $mimeTypes->guessMimeType($file->getPathname());
		$isImage = strpos($mimeType, 'image/') === 0;
		if (!$isImage) {
			// Fichier n'est une image
			return $this->json([
				"code" => 500,
				"value" => "Le fichier que vous avez envoyé n'est pas une image."
			]);
		}
		//
		$user = $user->find($this->getUser());
		$destination = $this->getParameter('kernel.project_dir') . '/public/images/uploads/profil_picture/' . $user->getId();
		$newFilename = $user->getId() . rand(0, 9999) . '.img';

		try {
			$dtPp->move(
				$destination,
				$newFilename
			);
		} catch (FileException $e) {
			return $this->json([
				"code" => 500,
				"value" => "Une erreur est survenue lors de l'upload de votre image."
			]);
		}
		$cloudinary = new Cloudinary(
			[
				'cloud' => [
					'cloud_name' => 'djaro8nwk',
					'api_key'    => '716759172429212',
					'api_secret' => 'A35hPbZP0NsjnMKrE9pLR-EHwiU',
				],
			]
		);

		$cloudinary->uploadApi()->upload(
			$destination . "/" . $newFilename,
			['public_id' => $newFilename, 'folder' => "profil_picture/" . $user->getId(),]
		);

		$urlCloudinary = $cloudinary->image("profil_picture/" . $user->getId() . "/" . $newFilename)->resize(Resize::fill(500, 500))->toUrl();
		// * On supprime la pp de l'utilisateur
		if (\file_exists($destination . "/" . $newFilename)) {
			\unlink($destination . "/" . $newFilename);
		}
		// On récupère le dernier cover de la publication pour la supprimer de Cloudinary : 
		if ($user->getProfilPicture()) {
			preg_match("/\/([^\/]*\.img)/", $user->getProfilPicture(), $matches);
			$result = $matches[1];
			$cloudinary->uploadApi()->destroy("profil_picture/" . $user->getId() . "/" . $result, ['invalidate' => true,]);
		}
		$user->setProfilPicture($urlCloudinary);
		$em->persist($user);
		$em->flush();
		return $this->json([
			"code" => 200,
			"value" => "Votre image de profil a bien été modifiée !",
			"cloudinary" => $urlCloudinary
		]);
	}
}
