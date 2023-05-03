<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationKeywordRepository;
use App\Repository\PublicationCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function searchIndex(Request $request, PublicationRepository $pRepo, PublicationKeywordRepository $pkwRepo, PublicationCategoryRepository $pcatRepo, UserRepository $userRepo): Response
    {

        // ! Recherche par publication ou par auteur
        $pubOrAuthor = $request->query->get('pubOrAuthor');
        $keywords = $request->query->get('keyword');
        $searchText = $request->query->get('searchText');
        if ($request->query->get('orderBy')) {
            $orderBy = $request->query->get('orderBy');
        } else {
            $orderBy = $request->query->get('orderByUser');
        }
        $notNull = $request->query->get('notNull');
        // !
        // * Recherche par publication
        // !
        if ($pubOrAuthor == "publication") {
            $sortBy = $request->query->get('sortBy');
            $category = $request->query->get('category');
            $timeShort = $request->query->get('timeShort');
            $timeMedium = $request->query->get('timeMedium');
            $timeLong = $request->query->get('timeLong');
            //  ! Traitement des variables
            if ($sortBy == 'published') {
                $sortByQuery = 'p.published_date';
            } elseif ($sortBy == 'time') {
                $sortByQuery = 'p.wordCount';
            } elseif ($sortBy == 'pop') {
                $sortByQuery = 'p.pop';
            } elseif ($sortBy == 'title') {
                $sortByQuery = 'p.title';
            } elseif ($sortBy == 'sheet') {
                $sortByQuery = 'pc.published';
            } else {
                $sortByQuery = 'p.published_date';
            }

            // ! Recherche

            // * On récupère les publications qui correspondent à la recherche
            $qb = $pRepo->createQueryBuilder('p')
                ->leftJoin('p.publicationChapters', 'pc')
                ->leftJoin('p.user', 'a')
                ->where('p.status = 2')
                ->andWhere('pc.status = 2');
            if ($category) {
                // Récupérer l'objet de catégorie par le slug
                $categoryObject = $pcatRepo->findOneBy(['slug' => $category]);

                if ($categoryObject) {
                    $qb->andWhere('p.category = :category')
                        ->setParameter('category', $categoryObject);
                }
            }
            $orX = $qb->expr()->orX(
                $qb->expr()->like('p.title', ':searchText'),
                $qb->expr()->like('p.summary', ':searchText'),
                $qb->expr()->like('a.username', ':searchText'),
                $qb->expr()->like('a.nickname', ':searchText')
            );
            $qb->andWhere($orX)
                ->setParameter('searchText', '%' . $searchText . '%');
            $qb->orderBy($sortByQuery, $orderBy);

            // Execute the query and fetch the results
            $results = $qb->getQuery()->getResult();

            // ! Filtrage des résultats par durée (checkbox)
            // 5000 mots = 20 minutes de lecture = court
            // 10000 mots = 40 minutes de lecture = moyen
            // 20000 mots = 80 minutes de lecture = long
            if (($timeShort and $timeMedium and $timeLong) or (!$timeShort and !$timeMedium and !$timeLong)) {
            } else {
                if ($timeShort and !$timeMedium and !$timeLong) {
                    // On supprime de results les publications qui ont un wordCount > 5000
                    $i = 0;
                    foreach ($results as $result) {
                        if ($result->getWordCount() > 5000) {
                            unset($results[$i]);
                        }
                        $i++;
                    }
                } elseif (!$timeShort and $timeMedium and !$timeLong) {
                    // On supprime de results les publications qui ont un wordCount < 5000 et de > 20000
                    $i = 0;
                    foreach ($results as $result) {
                        if ($result->getWordCount() < 5000 or $result->getWordCount() > 20000) {
                            unset($results[$i]);
                        }
                        $i++;
                    }
                } elseif (!$timeShort and !$timeMedium and $timeLong) {
                    // On supprime de results les publications qui ont un wordCount < 20000
                    $i = 0;
                    foreach ($results as $result) {
                        if ($result->getWordCount() < 20000) {
                            unset($results[$i]);
                        }
                        $i++;
                    }
                } elseif ($timeShort and $timeMedium and !$timeLong) {
                    // On supprime de results les publications qui ont un wordCount > 20000
                    $i = 0;
                    foreach ($results as $result) {
                        if ($result->getWordCount() > 20000) {
                            unset($results[$i]);
                        }
                        $i++;
                    }
                } elseif ($timeShort and !$timeMedium and $timeLong) {
                    // On supprime de results les publications qui ont un wordCount entre 10000 et 20000
                    $i = 0;
                    foreach ($results as $result) {
                        if ($result->getWordCount() > 5000 and $result->getWordCount() < 20000) {
                            unset($results[$i]);
                        }
                        $i++;
                    }
                } elseif (!$timeShort and $timeMedium and $timeLong) {
                    // On supprime de results les publications qui ont un wordCount < 5000
                    $i = 0;
                    foreach ($results as $result) {
                        if ($result->getWordCount() < 5000) {
                            unset($results[$i]);
                        }
                        $i++;
                    }
                }
            }

            // ! Filtr par nombre de likes
            if ($sortBy == 'like') {
                // Créez un tableau associatif pour stocker les résultats avec leurs likes
                $resultsWithLikes = [];

                foreach ($results as $result) {
                    $likes = 0;
                    foreach ($result->getPublicationChapters() as $chapter) {
                        if ($chapter->getStatus() == 2) {
                            $likes += count($chapter->getPublicationChapterLikes());
                        }
                    }
                    // Ajoutez cette donnée aux résultats de la recherche
                    $resultsWithLikes[] = ['result' => $result, 'likes' => $likes];
                }
                // Triez les résultats
                if ($orderBy == 'ASC') {
                    usort($resultsWithLikes, function ($a, $b) {
                        if ($a['likes'] == $b['likes']) {
                            // Si les deux publications ont le même nombre de likes, comparez leurs dates de publication
                            return $b['result']->getPublishedDate() <=> $a['result']->getPublishedDate();
                        }
                        return $a['likes'] <=> $b['likes'];
                    });
                } else {
                    usort($resultsWithLikes, function ($a, $b) {
                        if ($a['likes'] == $b['likes']) {
                            // Si les deux publications ont le même nombre de likes, comparez leurs dates de publication
                            return $b['result']->getPublishedDate() <=> $a['result']->getPublishedDate();
                        }
                        return $b['likes'] <=> $a['likes'];
                    });
                }
                if ($notNull) {
                    $resultsWithLikes = array_filter($resultsWithLikes, function ($resultWithLikes) {
                        return $resultWithLikes['likes'] > 0;
                    });
                }
                // Remplacez les résultats d'origine par ceux du tableau associatif
                $results = array_map(function ($resultWithLikes) {
                    return $resultWithLikes['result'];
                }, $resultsWithLikes);
            }
            // ! Filtr par nombre de chapitres
            if ($sortBy == 'nbrSheet') {
                // Créez un tableau associatif pour stocker les résultats avec leurs sheets
                $resultsWithSheets = [];

                foreach ($results as $result) {
                    $sheets = 0;
                    foreach ($result->getPublicationChapters() as $chapter) {
                        if ($chapter->getStatus() == 2) {
                            $sheets++;
                        }
                    }
                    // Ajoutez cette donnée aux résultats de la recherche
                    $resultsWithSheets[] = ['result' => $result, 'sheets' => $sheets];
                }
                // Triez les résultats
                if ($orderBy == 'ASC') {
                    usort($resultsWithSheets, function ($a, $b) {
                        if ($a['sheets'] == $b['sheets']) {
                            // Si les deux publications ont le même nombre de chapitres, comparez leurs dates de publication
                            return $b['result']->getPublishedDate() <=> $a['result']->getPublishedDate();
                        }
                        return $a['sheets'] <=> $b['sheets'];
                    });
                } else {
                    usort($resultsWithSheets, function ($a, $b) {
                        if ($a['sheets'] == $b['sheets']) {
                            // Si les deux publications ont le même nombre de chapitres, comparez leurs dates de publication
                            return $b['result']->getPublishedDate() <=> $a['result']->getPublishedDate();
                        }
                        return $b['sheets'] <=> $a['sheets'];
                    });
                }
                if ($notNull) {
                    $resultsWithSheets = array_filter($resultsWithSheets, function ($resultWithSheets) {
                        return $resultWithSheets['sheets'] > 0;
                    });
                }
                // Remplacez les résultats d'origine par ceux du tableau associatif
                $results = array_map(function ($resultWithSheets) {
                    return $resultWithSheets['result'];
                }, $resultsWithSheets);
            }

            // ! Filtr par nombre de lectures
            if ($sortBy == 'view') {
                // Créez un tableau associatif pour stocker les résultats avec leurs views
                $resultsWithViews = [];

                foreach ($results as $result) {
                    $views = 0;
                    foreach ($result->getPublicationChapters() as $chapter) {
                        if ($chapter->getStatus() == 2) {
                            $views += count($chapter->getPublicationChapterViews());
                        }
                    }
                    // Ajoutez cette donnée aux résultats de la recherche
                    $resultsWithViews[] = ['result' => $result, 'views' => $views];
                }
                // Triez les résultats
                if ($orderBy == 'ASC') {
                    usort($resultsWithViews, function ($a, $b) {
                        if ($a['views'] == $b['views']) {
                            // Si les deux publications ont le même nombre de vues, comparez leurs dates de publication
                            return $b['result']->getPublishedDate() <=> $a['result']->getPublishedDate();
                        }
                        return $a['views'] <=> $b['views'];
                    });
                } else {
                    usort($resultsWithViews, function ($a, $b) {
                        if ($a['views'] == $b['views']) {
                            // Si les deux publications ont le même nombre de vues, comparez leurs dates de publication
                            return $b['result']->getPublishedDate() <=> $a['result']->getPublishedDate();
                        }
                        return $b['views'] <=> $a['views'];
                    });
                }
                if ($notNull) {
                    $resultsWithViews = array_filter($resultsWithViews, function ($resultWithViews) {
                        return $resultWithViews['views'] > 0;
                    });
                }
                // Remplacez les résultats d'origine par ceux du tableau associatif
                $results = array_map(function ($resultWithViews) {
                    return $resultWithViews['result'];
                }, $resultsWithViews);
            }

            // ! Filtr par nombre de commentaires
            if ($sortBy == 'comment') {
                // Créez un tableau associatif pour stocker les résultats avec leurs commentaires
                $resultsWithComments = [];

                foreach ($results as $result) {
                    $comments = 0;
                    foreach ($result->getPublicationComments() as $comment) {
                        $comments += count($comment->getPublicationComments());
                    }
                    // Ajoutez cette donnée aux résultats de la recherche
                    $resultsWithComments[] = ['result' => $result, 'comments' => $comments];
                }
                // Triez les résultats
                if ($orderBy == 'ASC') {
                    usort($resultsWithComments, function ($a, $b) {
                        if ($a['comments'] == $b['comments']) {
                            // Si les deux publications ont le même nombre de commentaires, comparez leurs dates de publication
                            return $b['result']->getPublishedDate() <=> $a['result']->getPublishedDate();
                        }
                        return $a['comments'] <=> $b['comments'];
                    });
                } else {
                    usort($resultsWithComments, function ($a, $b) {
                        if ($a['comments'] == $b['comments']) {
                            // Si les deux publications ont le même nombre de commentaires, comparez leurs dates de publication
                            return $b['result']->getPublishedDate() <=> $a['result']->getPublishedDate();
                        }
                        return $b['comments'] <=> $a['comments'];
                    });
                }
                if ($notNull) {
                    $resultsWithComments = array_filter($resultsWithComments, function ($resultWithComments) {
                        return $resultWithComments['comments'] > 0;
                    });
                }
                // Remplacez les résultats d'origine par ceux du tableau associatif
                $results = array_map(function ($resultWithComments) {
                    return $resultWithComments['result'];
                }, $resultsWithComments);
            }
        } else {
            // !!!!!!!!!!!!!!!!!
            // * Recherche par auteur
            // !!!!!!!!!!!!!!!!!
            $sortBy = $request->query->get('sortByUser');
            $alreadyPublished = $request->query->get('alreadyPublished');
            if ($sortBy == 'alpha') {
                $sortByQuery = 'u.nickname';
            }
            if ($sortBy == 'joinDate') {
                $sortByQuery = 'u.join_date';
            } else {
                $sortByQuery = 'u.nickname';
            }
            $qb = $userRepo->createQueryBuilder('u')
                ->leftJoin('u.publications', 'p')
                ->leftJoin('p.publicationChapters', 'pc');

            $orX = $qb->expr()->orX(
                $qb->expr()->like('u.username', ':searchText'),
                $qb->expr()->like('u.nickname', ':searchText'),
            );

            $qb->where($orX)
                ->setParameter('searchText', '%' . $searchText . '%');

            if ($alreadyPublished) {
                $qb->andWhere('p.status = 2')
                    ->andWhere('pc.status = 2');
            }
            $qb->orderBy($sortByQuery, $orderBy);

            $results = $qb->getQuery()->getResult();
        }

        // ! Filtr par nombre de publications
        if ($sortBy == 'nbPub') {
            // Créez un tableau associatif pour stocker les résultats avec le nombre de récits publiés
            $resultsWithNbPub = [];

            foreach ($results as $result) {
                $publicationsStatus2 = [];

                foreach ($result->getPublications() as $publication) {
                    if ($publication->getStatus() == 2) {
                        $hasChapterStatus2 = false;
                        foreach ($publication->getPublicationChapters() as $chapter) {
                            if ($chapter->getStatus() == 2) {
                                $hasChapterStatus2 = true;
                                break;
                            }
                        }

                        if ($hasChapterStatus2) {
                            $publicationsStatus2[] = $publication;
                        }
                    }
                }
                // Ajoutez cette donnée aux résultats de la recherche
                $resultsWithNbPub[] = ['result' => $result, 'nbPub' => count($publicationsStatus2)];
            }
            // Triez les résultats (si le nombre de publications est le même, comparez les dates de publication et trier par publication la plus récente)
            if ($orderBy == 'ASC') {
                usort($resultsWithNbPub, function ($a, $b) {
                    if ($a['nbPub'] == $b['nbPub']) {
                        $aPublications = $a['result']->getPublications()->toArray();
                        $bPublications = $b['result']->getPublications()->toArray();

                        $aMostRecent = !empty($aPublications) ? max(array_map(function ($publication) {
                            return $publication->getPublishedDate();
                        }, $aPublications)) : null;

                        $bMostRecent = !empty($bPublications) ? max(array_map(function ($publication) {
                            return $publication->getPublishedDate();
                        }, $bPublications)) : null;

                        return $aMostRecent <=> $bMostRecent;
                    }
                    return $a['nbPub'] <=> $b['nbPub'];
                });
            } else {
                usort($resultsWithNbPub, function ($a, $b) {
                    if ($a['nbPub'] == $b['nbPub']) {
                        $aPublications = $a['result']->getPublications()->toArray();
                        $bPublications = $b['result']->getPublications()->toArray();

                        $aMostRecent = !empty($aPublications) ? max(array_map(function ($publication) {
                            return $publication->getPublishedDate();
                        }, $aPublications)) : null;

                        $bMostRecent = !empty($bPublications) ? max(array_map(function ($publication) {
                            return $publication->getPublishedDate();
                        }, $bPublications)) : null;

                        return $bMostRecent <=> $aMostRecent;
                    }
                    return $b['nbPub'] <=> $a['nbPub'];
                });
            }
            if ($notNull) {
                $resultsWithNbPub = array_filter($resultsWithNbPub, function ($resultWithNbPub) {
                    return $resultWithNbPub['nbPub'] > 0;
                });
            }

            // Remplacez les résultats d'origine par ceux du tableau associatif
            $results = array_map(function ($resultWithNbPub) {
                return $resultWithNbPub['result'];
            }, $resultsWithNbPub);
        }


        // ! MOTS CLÉS
        if ($pubOrAuthor == "publication" && $keywords) {
            // * On explode les mots clés, séparés par des virgules
            $explodedKeywords = explode(',', $keywords);
            // * Suppression des valeurs nulles ou vides
            $explodedKeywords = array_filter($explodedKeywords, function ($value) {
                return !empty(trim($value));
            });

            // * S'il y a des mots clés dans la requête, on supprime de $results toutes les publications qui ne contiennent pas ces mots clés
            foreach ($results as $key => $publication) {
                $publicationKeywords = $publication->getPublicationKeywords();
                $foundKeyword = false;

                foreach ($publicationKeywords as $pkw) {
                    if (in_array($pkw->getKeyword(), $explodedKeywords)) {
                        $foundKeyword = true;
                        break;
                    }
                }

                if (!$foundKeyword) {
                    unset($results[$key]);
                }
            }
        }



        // ! PAGINATION
        $nbr_by_page = 12;
        $page = $request->query->get('page') ?? 1;
        $count = count($results);
        if ($count > $nbr_by_page) {
            $countPage = ceil($count / $nbr_by_page);
            $start = ($page - 1) * $nbr_by_page;
        } else {
            $start = 0;
            $countPage = 1;
        }
        $results = array_slice($results, $start, $nbr_by_page);

        // Keywords


        return $this->render('search/search.html.twig', [
            'results' => $results,
            'countPage' => $countPage,
            'page' => $page,
        ]);
    }
    #[Route('/search/getkw', name: 'app_search_getkw', methods: ['POST'])]
    public function getKw(Request $request, PublicationKeywordRepository $pkwRepo, PublicationRepository $pRepo): Response
    {
        $publications = $pRepo->createQueryBuilder("p")
            ->leftJoin("p.publicationChapters", "pc")
            ->where("p.status = 2")
            ->andWhere("pc.status = 2")
            ->getQuery()
            ->getResult();

        // On récupère les keywords de chaque publication et on stocke dans un tableau
        $keywords = [];
        foreach ($publications as $publication) {
            foreach ($publication->getPublicationKeywords() as $keyword) {
                // On récupère l'ID du keyword, le count et le nom du keyword
                $id = $keyword->getId();
                $count = $keyword->getCount();
                $name = $keyword->getKeyword();
                // On stocke dans un tableau
                $keywords[$id] = [
                    "count" => $count,
                    "name" => $name,
                ];
            }
        }
        // On trie par "count" décroissant
        uasort($keywords, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        // retour en json
        return $this->json([
            "code" => 200,
            "keywords" => array_values($keywords),
        ], 200);
    }
}
