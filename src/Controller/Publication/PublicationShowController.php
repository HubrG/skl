<?php
// TODO : ajouter une colonne "clic" qui calcule ne nombre de clic sur un mot clé pour afficher les plus popualaires

namespace App\Controller\Publication;

use App\Services\PublicationPopularity;
use App\Services\PublicationDownloadPDF;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationKeywordRepository;
use App\Repository\PublicationCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicationShowController extends AbstractController
{

	private $publicationPopularity;

	private $publicationDownloadPDF;



	public function __construct(PublicationPopularity $publicationPopularity, PublicationDownloadPDF $publicationDownloadPDF)
	{
		$this->publicationPopularity = $publicationPopularity;
		$this->publicationDownloadPDF = $publicationDownloadPDF;
	}

	#[Route('/recits/{slug?}/{page<\d+>?}/{sortby?}/{order?}/{keystring?}', name: 'app_publication_show_all_category')]
	public function show_all(PublicationCategoryRepository $pcRepo, PublicationKeywordRepository $kwRepo, PublicationRepository $pRepo, $sortby = "p.pop", $page = 1, $slug = "all", $keystring = null, $order = "desc"): Response
	{

		// $p = $pRepo->findAll();
		// for ($i = 0; $i <h2 count($p); $i++) {
		// 	$pp = new Publication();
		// 	$pp = $p[$i];
		// 	$this->publicationPopularity->PublicationPopularity($pp);
		// }
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
				//!SECTION
				// if ($slug == "all") {
				// 	$qb = $pRepo->createQueryBuilder("p")
				// 		->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
				// 		->where("p.status = 2")
				// 		->andWhere("pch.status = 2")
				// 		->andWhere("p.category is not null")
				// 		->orderBy($sortby, $order)
				// 		->setFirstResult($start)
				// 		->setMaxResults($nbr_by_page);
				// } else {
				// 	$qb = $pRepo->createQueryBuilder("p")
				// 		->innerJoin("p.publicationChapters", "pch", "WITH", "pch.status = 2")
				// 		->innerJoin("p.category", "pc", "WITH", "pc.id = :category_id")
				// 		->where("p.status = 2")
				// 		->andWhere("pc.id = :category_id")
				// 		->setParameter("category_id", $pcRepo)
				// 		->orderBy($sortby, $order)
				// 		->setFirstResult($start)
				// 		->setMaxResults($nbr_by_page);
				// }
				if ($sortby == "p.pop") {
					$publications = $pRepo->findBy(["id" => $publicationsAll], ["pop" => $order], $nbr_by_page, $start);
				} else {
					$publications = $pRepo->findBy(["id" => $publicationsAll], ["published_date" => $order], $nbr_by_page, $start);
				}
				try {
					// $publications = $qb->getQuery()->getResult();
					// dd("Start: " . $start, "End: " . $end, "CountPage: " . $countPage, "Count Pub: " . $count, "Nbr by page: " . $nbr_by_page, "Page: " . $page, "Slug: " . $slug, "Keystring: " . $keystring, "Order: " . $order, "Sort by: " . $sortby, $publicationsAll);
				} catch (\Exception $e) {
					return $this->redirectToRoute('app_home');
				}
				//!SECTION
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
				// if ($slug != "all") {
				// 	// * ... on enlève les publications qui ne sont pas de la catégorie et qui n'ont pas de chapitre publié
				// 	$publications = array_filter($publications, function ($p) use ($pcRepo) {
				// 		return $p->getCategory() == $pcRepo;
				// 	});
				// 	// * On vérifie que la publication a au moins un chapitre publié
				// 	$publications = array_filter($publications, function ($p) {
				// 		$chapters = $p->getPublicationChapters();
				// 		if ($chapters->count() > 0) {
				// 			foreach ($chapters as $chapter) {
				// 				if ($chapter->getStatus() == 2) {
				// 					return true;
				// 				}
				// 			}
				// 		}
				// 		return false;
				// 	});
				// } else {
				// 	// * On vérifie que la publication a au moins un chapitre publié
				// 	$publications = array_filter($publications, function ($p) {
				// 		$chapters = $p->getPublicationChapters();
				// 		if ($chapters->count() > 0) {
				// 			foreach ($chapters as $chapter) {
				// 				if ($chapter->getStatus() == 2) {
				// 					return true;
				// 				}
				// 			}
				// 		}
				// 		return false;
				// 	});
				// // }
				// // * ... et on enlève les doublons en récupérant les ID unique de $publications
				// // ! pagination
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
				'limit' => $nbr_by_page // Retourne l'ordre d'affichage
			]);
		}
	}

	#[Route('/recit/{id<\d+>}/{slug}', name: 'app_publication_show_one')]
	public function show_one(PublicationRepository $pRepo, PublicationChapterRepository $pchRepo, $id = null, $slug = null): Response
	{


		$publication = $pRepo->find($id);
		$orderChap = $pchRepo->findOneBy(["publication" => $publication, "order_display" => 0, "status" => 2]);
		$chapters = $pchRepo->findBy(["publication" => $publication, "status" => 2], ["order_display" => "ASC"]);

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

			return $this->render('publication/show_one.html.twig', [
				'pubShow' => $publication,
				'orderChap' => $orderChap,
				'chapShow' => $chapters
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
}
