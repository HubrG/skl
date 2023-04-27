<?php

namespace App\Controller;

use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/recherche', name: 'app_search')]
    public function index(Request $request, PublicationRepository $pRepo): Response
    {
        $searchText = $request->query->get('searchText');
        $publications = $request->query->get('publications');
        $authors = $request->query->get('authors');
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
            $sortByQuery = 'p.published_date';
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
        // * On réparti les mots de la recherche dans un tableau
        $searchText = explode(' ', $searchText);
        // * On supprime les espaces vides
        $searchText = array_filter($searchText);
        // * On supprime les doublons
        $searchText = array_unique($searchText);

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
            ->setParameter('searchText', '%' . implode('%', $searchText) . '%');
        $qb->orderBy($sortByQuery, $orderBy);

        // Execute the query and fetch the results
        $results = $qb->getQuery()->getResult();


        // ! Filtr par nombre de likes
        if ($sortBy == 'like') {
            // Si la publication est publiée et que le chapitre est publié, on récupère le nombre de like par chapitre dans publicationChapterLikes, on ajoute 1 au nombre
            $i = 0;
            foreach ($results as $result) {
                $likes = 0;
                foreach ($result->getPublicationChapters() as $chapter) {
                    if ($chapter->getStatus() == 2) {
                        $likes += count($chapter->getPublicationChapterLikes());
                    }
                }
                // On ajoute cette donnée aux résultats de la recherche
                $results[$i]->likes = $likes;
                $i++;
            }
            // On trie les résultats
            if ($orderBy == 'ASC') {
                usort($results, function ($a, $b) {
                    if ($a->likes == $b->likes) {
                        // Si les deux publications ont le même nombre de likes, comparez leurs dates de publication
                        return $b->getPublishedDate() <=> $a->getPublishedDate();
                    }
                    return $a->likes <=> $b->likes;
                });
            } else {
                usort($results, function ($a, $b) {
                    if ($a->likes == $b->likes) {
                        // Si les deux publications ont le même nombre de likes, comparez leurs dates de publication
                        return $b->getPublishedDate() <=> $a->getPublishedDate();
                    }
                    return $b->likes <=> $a->likes;
                });
            }
            if ($notNull) {
                $results = array_filter($results, function ($result) {
                    return $result->likes > 0;
                });
            }
        }
        // ! Filtr par nombre de chapitres
        if ($sortBy == 'nbrSheet') {
            // Si la publication est publiée et que le chapitre est publié, on ajoute 1 au nombre de chapitres
            $i = 0;
            foreach ($results as $result) {
                $sheets = 0;
                foreach ($result->getPublicationChapters() as $chapter) {
                    if ($chapter->getStatus() == 2) {
                        $sheets++;
                    }
                }
                // On ajoute cette donnée aux résultats de la recherche
                $results[$i]->sheets = $sheets;
                $i++;
            }
            // On trie les résultats
            if ($orderBy == 'ASC') {
                usort($results, function ($a, $b) {
                    if ($a->sheets == $b->sheets) {
                        // Si les deux publications ont le même nombre de chapitres, comparez leurs dates de publication
                        return $b->getPublishedDate() <=> $a->getPublishedDate();
                    }
                    return $a->sheets <=> $b->sheets;
                });
            } else {
                usort($results, function ($a, $b) {
                    if ($a->sheets == $b->sheets) {
                        // Si les deux publications ont le même nombre de chapitres, comparez leurs dates de publication
                        return $b->getPublishedDate() <=> $a->getPublishedDate();
                    }
                    return $b->sheets <=> $a->sheets;
                });
            }
            if ($notNull) {
                $results = array_filter($results, function ($result) {
                    return $result->sheets > 0;
                });
            }
        }
        // ! Filtr par nombre de lectures
        if ($sortBy == 'view') {
            // Si la publication est publiée et que le chapitre est publié, on ajoute 1 au nombre de lectures
            $i = 0;
            foreach ($results as $result) {
                $views = 0;
                foreach ($result->getPublicationChapters() as $chapter) {
                    if ($chapter->getStatus() == 2) {
                        $views += count($chapter->getPublicationChapterViews());
                    }
                }
                // On ajoute cette donnée aux résultats de la recherche
                $results[$i]->views = $views;
                $i++;
            }
            // On trie les résultats
            if ($orderBy == 'ASC') {
                usort($results, function ($a, $b) {
                    if ($a->views == $b->views) {
                        // Si les deux publications ont le même nombre de lectures, comparez leurs dates de publication
                        return $b->getPublishedDate() <=> $a->getPublishedDate();
                    }
                    return $a->views <=> $b->views;
                });
            } else {
                usort($results, function ($a, $b) {
                    if ($a->views == $b->views) {
                        // Si les deux publications ont le même nombre de lectures, comparez leurs dates de publication
                        return $b->getPublishedDate() <=> $a->getPublishedDate();
                    }
                    return $b->views <=> $a->views;
                });
            }
            if ($notNull) {
                $results = array_filter($results, function ($result) {
                    return $result->views > 0;
                });
            }
        }
        // ! Filtr par nombre de commentaires
        if ($sortBy == 'comment') {
            // Si la publication est publiée et que le chapitre est publié, on ajoute 1 au nombre de commentaires
            $i = 0;
            foreach ($results as $result) {
                $comments = 0;
                foreach ($result->getPublicationComments() as $comment) {
                    $comments += count($comment->getPublicationComments());
                }
                // On ajoute cette donnée aux résultats de la recherche
                $results[$i]->comments = $comments;
                $i++;
            }
            // On trie les résultats
            if ($orderBy == 'asc') {
                usort($results, function ($a, $b) {
                    return $a->comments <=> $b->comments;
                });
            } else {
                usort($results, function ($a, $b) {
                    return $b->comments <=> $a->comments;
                });
            }
            if ($notNull) {
                $results = array_filter($results, function ($result) {
                    return $result->comments > 0;
                });
            }
        }
        // ! Filtr par durée
        // On récupère tous les chapitres des publications qui correspondent à la recherche, on calcul le nombre de mots et on les ajoute à la publication
        $i = 0;
        foreach ($results as $result) {
            $chapters = $result->getPublicationChapters();
            $words = 0;
            foreach ($chapters as $chapter) {
                if ($chapter->getStatus() == 2) {
                    $words += str_word_count($chapter->getContent());
                }
            }
            // On ajoute cette donnée aux résultats de la recherche
            $results[$i]->words = $words;
            $i++;
        }
        if ($sortBy == 'time') {
            if ($orderBy == 'asc') {
                usort($results, function ($a, $b) {
                    return $a->words <=> $b->words;
                });
            } else {
                usort($results, function ($a, $b) {
                    return $b->words <=> $a->words;
                });
            }
        }
        // ! Classement par durée (checkboxes)
        if (($timeShort and $timeMedium and $timeLong)) {
        } else {
            if ($timeShort and !$timeMedium and !$timeLong) {
                $results = array_filter($results, function ($result) {
                    return $result->words <= 2500;
                });
            } elseif (!$timeShort and $timeMedium and !$timeLong) {
                $results = array_filter($results, function ($result) {
                    return $result->words > 2500 and $result->words <= 5000;
                });
            } elseif (!$timeShort and !$timeMedium and $timeLong) {
                $results = array_filter($results, function ($result) {
                    return $result->words > 5000;
                });
            } elseif ($timeShort and $timeMedium and !$timeLong) {
                $results = array_filter($results, function ($result) {
                    return $result->words <= 5000;
                });
            } elseif ($timeShort and !$timeMedium and $timeLong) {
                $results = array_filter($results, function ($result) {
                    return $result->words <= 2500 or $result->words > 5000;
                });
            } elseif (!$timeShort and $timeMedium and $timeLong) {
                $results = array_filter($results, function ($result) {
                    return $result->words > 2500;
                });
            }
        }

        return $this->render('search/search.html.twig', [
            'results' => $results
        ]);
    }
}
