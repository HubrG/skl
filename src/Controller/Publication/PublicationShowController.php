<?php
// TODO : ajouter une colonne "clic" qui calcule ne nombre de clic sur un mot clé pour afficher les plus popualaires

namespace App\Controller\Publication;

use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationKeywordRepository;
use App\Repository\PublicationCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicationShowController extends AbstractController
{
	#[Route('/recits/{slug?}/{page<\d+>?}/{order?}/{keystring?}', name: 'app_publication_show_all_category')]
	public function show_all(PublicationCategoryRepository $pcRepo, PublicationKeywordRepository $kwRepo, PublicationRepository $pRepo, $page = null, $slug = null, $keystring = null, $order = null): Response
	{
		// * On set les variables si elles ne sont pas dans l'url
		if (!$page) {
			$page = 1;
		}
		if (!$order) {
			$order = "desc";
		}
		if (!$slug) {
			$slug = "all"; // S'il n'y a pas de slug, on affiche toutes les catégories
		}
		if ($slug != "all") {
			$pcRepo = $pcRepo->findOneBy(["slug" => $slug]);
		} else {
			$pcRepo = $pcRepo->findAll();
		}
		// ! Si il y a bien des publications dans la catégorie sélectionnée...
		if ($pcRepo) {
			// ! On prépare la pagination et on récupère les keywords de la catégorie sélectionnée
			$nbr_by_page = 10;
			$count = count($publicationsAll = $pRepo->findBy(["category" => $pcRepo, "status" => 2]));
			// ! On chercher les keywords
			// * S'il n'y a pas de slug dans l'url, on renvoie les keywords liés à toutes les publications
			if ($slug != "all") {
				// * On trouve les keywords de toutes les publications de la catégorie sélectionnée, et on les tri par ordre du plus utilisé au moins utilisé
				$publicationKw = $this->keyw_sort($publicationsAll);
			}
			// * Sinon, on renvoie tous les keywords de la base de données
			else {
				$publicationKw = $kwRepo->findBy([], ["count" => "desc"]);
			}
			// ! On cherche les publications
			// * S'il n'y a pas de keywords dans l'url, on renvoie toutes les publications
			if (!$keystring) {
				$countPage = $count / $nbr_by_page;
				$countPage = ceil($countPage);
				$page = $page - 1;
				$publications = $pRepo->findBy(["category" => $pcRepo, "status" => 2], ["published_date" => $order], $nbr_by_page, $page * $nbr_by_page);
				$keywString = null;
			}
			// * Si il y a des keywords dans l'url, on renvoie toutes les publications qui ont au moins un des keywords
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
				if ($slug != "all") {
					// * ... on enlève les publications qui ne sont pas de la catégorie
					$publications = array_filter($publications, function ($p) use ($pcRepo) {
						return $p->getCategory() == $pcRepo;
					});
				}
				// * ... et on enlève les doublons en récupérant les ID unique de $publications
				$publicationsAll = $pRepo->findBy(["id" => $publications, "status" => 2]);
				// ! pagination
				$nbr_by_page = 10;
				$count = count($publicationsAll);
				$countPage = $count / $nbr_by_page;
				$countPage = ceil($countPage);
				$page = $page - 1;
				// *
				$publications = $pRepo->findBy(["id" => $publications, "status" => 2], ["published_date" => $order], $nbr_by_page, $page * $nbr_by_page);
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
				'page' => $page + 1, // Retourne la page actuelle
				'orderSort' => $order // Retourne l'ordre d'affichage
			]);
		}
		// ! s'il n'y a pas de publications dans la catégorie sélectionnée... 
		else {
			return $this->redirectToRoute("app_home", [], Response::HTTP_SEE_OTHER);
		}
	}
	#[Route('/recit/{id<\d+>}/{slug}', name: 'app_publication_show_one')]
	public function show_one(PublicationRepository $pRepo, PublicationChapterRepository $pchRepo, $id = null, $slug = null): Response
	{
		$publication = $pRepo->find($id);
		$orderChap = $pchRepo->findOneBy(["publication" => $publication, "order_display" => 0, "status" => 2]);
		return $this->render('publication/show_one.html.twig', [
			'pubShow' => $publication,
			'orderChap' => $orderChap
		]);
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
