<?php
// TODO : ajouter une colonne "clic" qui calcule ne nombre de clic sur un mot clé pour afficher les plus popualaires

namespace App\Controller\Publication;

use App\Entity\PublicationFollow;
use App\Entity\PublicationBookmark;
use App\Form\PublicationCommentType;
use App\Services\NotificationSystem;
use App\Entity\PublicationCommentLike;
use App\Services\PublicationPopularity;
use App\Services\PublicationDownloadPDF;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PublicationFollowRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationCommentRepository;
use App\Repository\PublicationKeywordRepository;
use App\Repository\PublicationCategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Gzip;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicationShowController extends AbstractController
{

	private $publicationPopularity;

	private $publicationDownloadPDF;

	private $notificationSystem;

	public function __construct(NotificationSystem $notificationSystem, PublicationPopularity $publicationPopularity, PublicationDownloadPDF $publicationDownloadPDF)
	{
		$this->publicationPopularity = $publicationPopularity;
		$this->publicationDownloadPDF = $publicationDownloadPDF;
		$this->notificationSystem = $notificationSystem;
	}

	#[Route('/recits/{slug?}/{page<\d+>?}/{sortby?}/{order?}/{keystring?}', name: 'app_publication_show_all_category')]
	public function show_all(SessionInterface $session, PublicationCategoryRepository $pcRepo, PublicationKeywordRepository $kwRepo, PublicationRepository $pRepo, $sortby = "p.pop", $page = 1, $slug = "all", $keystring = null, $order = "desc"): Response
	{
		// * On set les variables si elles ne sont pas dans l'url
		$nbr_by_page = 10;
		$page = $page ?? 1;
		$order = $order ?? "desc";
		$slug = $slug ?? "all";
		if ($sortby == "published") {
			$sortby = "p.published_date";
		} else {
			$sortby = "p.pop";
		}
		$pcRepo = ($slug != "all") ? $pcRepo->findOneBy(["slug" => $slug]) : $pcRepo->findAll();
		if ($slug != "all") {
			$qb = $pRepo->createQueryBuilder("p")
				->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
				->innerJoin("p.category", "pc", "WITH", "pc.id = :category_id")
				->where("p.status = 2")
				->andWhere("pc.id = :category_id")
				->setParameter("category_id", $pcRepo);
		} else {
			$qb = $pRepo->createQueryBuilder("p")
				->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
				->where("p.status = 2")
				->andWhere("p.category is not null");
		}
		try {
			$count = count($qb->getQuery()->getResult());
			$publicationsAll = $qb->getQuery()->getResult();
		} catch (\Exception $e) {
			return $this->redirectToRoute('app_home');
		}
		if (!$pcRepo) {
			return $this->redirectToRoute("app_home", [], Response::HTTP_SEE_OTHER);
			// ! Si il y a bien des publications dans la catégorie sélectionnée...  On cherche les keywords
		} else {
			// * S'il n'y a pas de slug dans l'url, on renvoie les keywords liés à toutes les publications
			if ($slug != "all") {
				// * On trouve les keywords de toutes les publications de la catégorie sélectionnée, et on les tri par ordre du plus utilisé au moins utilisé
				$publicationKw = $this->keyw_sort($publicationsAll);
			}
			// * Sinon, on renvoie tous les keywords de la base de données
			else {
				$publicationKw = $this->keyw_sort($publicationsAll);
			}
			// ! On cherche les publications
			// * S'il n'y a pas de keywords dans l'url, on renvoie toutes les publications
			if (!$keystring) {
				if ($count > $nbr_by_page) {
					$countPage = ceil($count / $nbr_by_page);
					$start = ($page - 1) * $nbr_by_page;
					$end = min($start + $nbr_by_page, $count);
				} else {
					$start = 0;
					$countPage = 1;
					$end = $count;
				}
				if ($sortby == "p.pop") {
					$publications = $pRepo->findBy(["id" => $publicationsAll], ["pop" => $order], $nbr_by_page, $start);
				} else {
					$publications = $pRepo->findBy(["id" => $publicationsAll], ["published_date" => $order], $nbr_by_page, $start);
				}
				$keywString = null;
			}
			//  Si il y a des keywords dans l'url, on renvoie toutes les publications qui ont au moins un des keywords
			else {
				// * On récupère les keywords dans l'url et on les transforme en tableau
				$keyw = explode("—", $keystring);
				// * on supprime les keywords doublons
				$keyw = array_unique($keyw); // return : [0 => "keyword1", 1 => "keyword2"]
				// * On reconstitue la chaine de caractères des keywords
				$keywString = implode("—", $keyw); // return : "keyword1—keyword2"
				// * On recherche tous les keywords correspondants dans le repo des keywords
				$kw = $kwRepo->findBy(["keyword" => $keyw]);
				// *
				// * Ensuite, on recherche toutes les publications en relation avec les keywords de la variable $kw...
				$publications = [];
				foreach ($kw as $k) {
					$pubs = $k->getPublication();
					foreach ($pubs as $p) {
						$publications[] = $p;
					}
				}
				// ! pagination
				$count = count($publications);
				if ($count > $nbr_by_page) {
					$countPage = ceil($count / $nbr_by_page);
					$start = ($page - 1) * $nbr_by_page;
					$end = min($start + $nbr_by_page, $count);
				} else {
					$start = 0;
					$countPage = 1;
					$end = $count;
				}
				// *
				$publications = $pRepo->findBy(["id" => $publications], ["published_date" => $order], $nbr_by_page, $start);
				$count = count($publications);
			}
			// * 
			// ! render
			return $this->render('publication/show_all.html.twig', [
				'pubShow' => $publications, // Retourne toutes les publications
				'pubShowKw' => $publicationKw, // Retourne tous les keywords des publications affichées
				'kwString' => $keywString, // Retourne tous les keywords de la recherche
				'category' => $pcRepo, // Retourne la catégorie
				'count' => $count, // Retourne le nombre de publications
				'countPage' => $countPage, // Retourne le nombre de pages
				'page' => $page, // Retourne la page actuelle
				'orderSort' => $order, // Retourne l'ordre d'affichage
				'limit' => $nbr_by_page, // Retourne l'ordre d'affichage
				"canonicalUrl" => $this->generateUrl('app_publication_show_all_category', array(), true)

			]);
		}
	}

	#[Route('/recit/{id<\d+>}/{slug}/{nbrShowCom?}', name: 'app_publication_show_one')]
	public function show_one(Request $request, PublicationCommentRepository $pcomRepo, EntityManagerInterface $em, PublicationRepository $pRepo, PublicationChapterRepository $pchRepo, $nbrShowCom = 10, $id = null, $slug = null): Response
	{

		$nbrShowCom = $nbrShowCom ?? 10;
		$publication = $pRepo->find($id);
		// * on récupère le premier chapitre de la publication par order_display (le plus petit et le premier) avec un querybuilder
		$orderChap = $pchRepo->createQueryBuilder('pch')
			->where('pch.publication = :publication')
			->andWhere('pch.status = :status')
			->setParameter('publication', $publication)
			->setParameter('status', 2)
			->orderBy('pch.order_display', 'ASC')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
		$chapters = $pchRepo->findBy(["publication" => $publication, "status" => 2], ["order_display" => "ASC"]);

		if ($publication->getStatus() < 2 && $publication->getUser() != $this->getUser()) {
			return $this->redirectToRoute('app_home');
		}
		if (!$chapters) {
			return $this->redirectToRoute('app_home');
		}

		if ($slug == "download") {

			$this->publicationDownloadPDF->PublicationDownloadPDF($id); // download PDF
			return $this->redirectToRoute('app_publication_show_one', [
				'id' => $id,
				'slug' => $publication->getSlug()
			]);
		} else {
			$form = $this->createForm(PublicationCommentType::class);
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {
				$comment = $form->getData();
				$comment->setUser($this->getUser());
				$comment->setPublication($publication);
				$comment->setPublishedAt(new \DateTimeImmutable());
				$em->persist($comment);
				$em->flush();
				$this->addFlash('success', 'Votre commentaire a bien été envoyé !');
				// Mise à jour de la popularité
				$this->publicationPopularity->PublicationPopularity($comment->getPublication());
				// Envoi d'une notification
				$this->notificationSystem->addNotification(1, $comment->getPublication()->getUser(), $this->getUser(), $comment);
				//
				return $this->redirectToRoute('app_publication_show_one', [
					'id' => $id,
					'slug' => $publication->getSlug()
				]);
			} elseif ($form->isSubmitted() && $form->isValid() && !$this->getUser()) {
				$this->addFlash('danger', 'Vous devez être connecté pour poster un commentaire !');
				return $this->redirectToRoute('app_publication_show_one', [
					'id' => $id,
					'slug' => $publication->getSlug()
				]);
			}
			$comments = $pcomRepo->findBy(["publication" => $publication, "chapter" => null], ["published_at" => "DESC"]);
			$nbrComments = count($comments);
			return $this->render('publication/show_one.html.twig', [
				'pubShow' => $publication,
				'orderChap' => $orderChap,
				'chapShow' => $chapters,
				'formQuote' => $form,
				'pCom' => $comments,
				'nbrCom' => $nbrComments,
				'nbrShowCom' => $nbrShowCom,
				"canonicalUrl" => $this->generateUrl('app_publication_show_one', ["id" => $id, "slug" => $slug], true)
			]);
		}
	}
	public function keyw_sort($publications)
	{
		$keywords = array();
		foreach ($publications as $p) {
			$pubKw = $p->getPublicationKeywords();
			foreach ($pubKw as $k) {
				$keywords[] = ["keyword" => $k->getKeyword(), "count" => $k->getCount()];
			}
		}
		// Compter le nombre d'occurrences de chaque keyword
		$compte_keyw = array_count_values(array_column($keywords, 'keyword'));
		// Ajouter le nombre d'occurrences à chaque élément du tableau
		foreach ($keywords as &$ligne) {
			$ligne["occ"] = $compte_keyw[$ligne["keyword"]];
		}
		// * On trie le tableau $keywords par ordre décroissant de count
		usort($keywords, function ($a, $b) {
			return $b['occ'] <=> $a['occ'];
		});
		return $keywords;
	}

	#[Route('/recit/follow/{id}', name: 'app_publication_follow', methods: ['POST'])]
	public function followPublication(Request $request, PublicationRepository $pRepo, NotificationRepository $nRepo, EntityManagerInterface $em, $id): response
	{
		// * On récupère la publication
		$pub = $pRepo->find($id);
		// * Si la publication existe et que l'auteur du follow n'est pas l'auteur de la publication
		if ($pub and $pub->getUser() != $this->getUser()) {
			// * On vérifie que la publication n'a pas déjà été follow par l'utilisateur
			$follow = $pub->getPublicationFollows()->filter(function ($follow) {
				return $follow->getUser() == $this->getUser();
			})->first();
			// * Si le follower existe déjà, on le supprime
			if ($follow) {
				$em->remove($follow);
				$em->flush();
				// On supprime les notifications de type 8 avec l'user $this->getUser() qui concerne la publication $pub
				$notifications = $nRepo->findBy(["type" => 8, "from_user" => $this->getUser(), "publication_follow_add" => $pub]);
				foreach ($notifications as $n) {
					$em->remove($n);
					$em->flush();
				}
				//
				return $this->json([
					'code' => 200,
					'message' => '
					<i class="fa-regular fa-tag"></i>
					<strong>Vous ne suivez plus ce récit</strong><br>
					Vous ne recevrez plus de notification à chaque nouvelle feuille publiée'
				], 200);
			}
			// * Sinon, on ajoute le follower
			$follow = new PublicationFollow();
			$follow->setUser($this->getUser())
				->setPublication($pub)
				->setCreatedAt(new \DateTimeImmutable());
			$em->persist($follow);
			$em->flush();

			// * On met à jour la popularité de la publication
			// $this->publicationPopularity->PublicationPopularity($comment->getChapter()->getPublication());
			//
		} else {
			return $this->json([
				'code' => 403,
				'message' => 'Vous n\'avez pas le droit de suivre ce récit',
			], 403);
		}
		// * Ajout d'une notification
		$this->notificationSystem->addNotification(8, $pub->getUser(), $this->getUser(), $pub);
		//
		return $this->json([
			'code' => 200,
			'message' => '
			<i class="fa-regular fa-light fa-tags"></i>
			<strong>Vous suivez ce récit</strong><br>
			 Vous recevrez une notification à chaque nouvelle feuille publiée'

		], 200);
	}
	#[Route('/recit/addcollection/{id}', name: 'app_publication_add_collection', methods: ['POST'])]
	public function addPublicationCollection(Request $request, PublicationRepository $pRepo, NotificationRepository $nRepo, EntityManagerInterface $em, $id): response
	{
		// * On récupère la publication
		$pub = $pRepo->find($id);
		// * Si la publication existe et que l'auteur de l'ajout à la collection n'est pas l'auteur de la publication
		if ($pub and $pub->getUser() != $this->getUser()) {
			// * On vérifie que la publication n'a pas déjà été follow par l'utilisateur
			$collection = $pub->getPublicationBookmarks()->filter(function ($collection) {
				return $collection->getUser() == $this->getUser() && $collection->getChapter() === NULL;
			})->first();
			// * Si le collectionneur existe déjà, on le supprime (uniquement si l'entrée n'a pas de "chapter_id" de renseigné)
			if ($collection) {

				$em->remove($collection);
				$em->flush();

				return $this->json([
					'code' => 200,
					'message' => '
					<i class="fa-regular fa-folder-bookmark"></i>
					<strong>Récit retiré de votre collection</strong>'
				], 200);
			}
			// * Sinon, on ajoute le collectionneur
			$collection = new PublicationBookmark();
			$collection->setUser($this->getUser())
				->setPublication($pub)
				->setCreatedAt(new \DateTimeImmutable());
			$em->persist($collection);
			$em->flush();

			// * On met à jour la popularité de la publication
			$this->publicationPopularity->PublicationPopularity($pub);
			//
		} else {
			return $this->json([
				'code' => 403,
				'message' => 'Vous n\'avez pas le droit de suivre ce récit',
			], 403);
		}
		// * Ajout d'une notification
		$this->notificationSystem->addNotification(4, $pub->getUser(), $this->getUser(), $collection);
		//
		return $this->json([
			'code' => 200,
			'message' => '
			<i class="fa-duotone fa-folder-bookmark"></i>
			<strong>Récit ajouté à votre collection</strong>'

		], 200);
	}
}
