<?php
// TODO : ajouter une colonne "clic" qui calcule ne nombre de clic sur un mot clé pour afficher les plus popualaires

namespace App\Controller\Publication;

use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationKeywordRepository;
use App\Repository\PublicationCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicationShowController extends AbstractController
{
    #[Route('/stories/{slug}/{page?}/{keystring?}', name: 'app_publication_show_all_category')]
    public function show_all(PublicationCategoryRepository $pcRepo, PublicationKeywordRepository $kwRepo, PublicationRepository $pRepo, $page = null, $slug = null, $keystring = null): Response
    {
        $pcRepo = $pcRepo->findOneBy(["slug" => $slug]);
        // ! Si il y a bien des publications dans la catégorie sélectionnée...
        if ($pcRepo) {
            // * Si il y a des keywords dans l'url
            if ($keystring) {
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
                // * ... on enlève les publications qui ne sont pas de la catégorie
                $publications = array_filter($publications, function ($p) use ($pcRepo) {
                    return $p->getCategory() == $pcRepo;
                });
                // * ... et on enlève les doublons
                //
                $publicationsAll = $pRepo->findBy(["id" => $publications, "status" => 2]);
                // ! pagination
                $nbr_by_page = 10;
                $count = count($publicationsAll);
                $countPage = $count / $nbr_by_page;
                $countPage = ceil($countPage);
                if ($page) {
                    $page = $page - 1;
                    $publications = $pRepo->findBy(["id" => $publications, "status" => 2], ["published_date" => "DESC"], $nbr_by_page, $page * $nbr_by_page);
                } else {
                    $publications = $pRepo->findBy(["id" => $publications, "status" => 2], ["published_date" => "DESC"], $nbr_by_page);
                }
                // * tri des mots clés
                $keywords = $this->keyw_sort($publicationsAll);
            }
            // ! s'il n'y a pas de publications dans la catégorie sélectionnée... 
            else {
                // ! pagination
                $nbr_by_page = 10;
                $count = count($publicationsAll = $pRepo->findBy(["category" => $pcRepo->getId(), "status" => 2]));
                $countPage = $count / $nbr_by_page;
                $countPage = ceil($countPage);
                if ($page) {
                    $page = $page - 1;
                    $publications = $pRepo->findBy(["category" => $pcRepo->getId(), "status" => 2], ["published_date" => "DESC"], $nbr_by_page, $page * $nbr_by_page);
                } else {
                    $publications = $pRepo->findBy(["category" => $pcRepo->getId(), "status" => 2], ["published_date" => "DESC"], $nbr_by_page);
                }
                $keywString = null;
                // * tri des mots clés
                $keywords = $this->keyw_sort($publicationsAll);
            }
            // ! render
            return $this->render('publication/show_all.html.twig', [
                'pubShow' => $publications, // affiche toutes les publications
                'kwString' => $keywString, // affiche les keywords de la recherche
                'keywords' => $keywords, // affiche les keywords les plus utilisés
                'category' => $pcRepo, // affiche la catégorie
                'count' => $count, // affiche le nombre de publications
                'countPage' => $countPage // affiche le nombre de pages
            ]);
        } else {
            return $this->redirectToRoute("app_home", [], Response::HTTP_SEE_OTHER);
        }
    }
    #[Route('/story/{id}/{slug}', name: 'app_publication_show_one')]
    public function show_one($pubId = null): Response
    {
        return $this->renderForm('publication/show_one.html.twig', [
            'pubShow' => "ok"
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
        $compte_villes = array_count_values(array_column($keywords, 'keyword'));
        // Ajouter le nombre d'occurrences à chaque élément du tableau
        foreach ($keywords as &$ligne) {
            $ligne["occ"] = $compte_villes[$ligne["keyword"]];
        }
        // * On trie le tableau $keywords par ordre décroissant de count
        usort($keywords, function ($a, $b) {
            return $b['occ'] <=> $a['occ'];
        });
        return $keywords;
    }
}
