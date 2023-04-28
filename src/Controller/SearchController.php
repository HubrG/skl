<?php

namespace App\Controller;

use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function searchIndex(Request $request, PublicationRepository $pRepo): Response
    {
        $searchText = $request->query->get('searchText');
        $timeShort = $request->query->get('timeShort');
        $timeMedium = $request->query->get('timeMedium');
        $notNull = $request->query->get('notNull');
        $timeLong = $request->query->get('timeLong');
        $sortBy = $request->query->get('sortBy');
        $orderBy = $request->query->get('orderBy');
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
                    if ($result->getWordCount() > 10000 and $result->getWordCount() < 20000) {
                        unset($results[$i]);
                    }
                    $i++;
                }
            } elseif (!$timeShort and $timeMedium and $timeLong) {
                // On supprime de results les publications qui ont un wordCount < 10000
                $i = 0;
                foreach ($results as $result) {
                    if ($result->getWordCount() < 10000) {
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


        return $this->render('search/search.html.twig', [
            'results' => $results
        ]);
    }
}
