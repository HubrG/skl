<?php

namespace App\Twig;

use DateTime;
use App\Entity\User;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class TwigUserInfo extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter("nbrPublication", [$this, "nbrPublication"]),
            new TwigFilter("mostPopularPublication", [$this, "mostPopularPublication"])
        ];
    }
    public function nbrPublication(User $user)
    {
        $publicationsStatus2 = [];

        foreach ($user->getPublications() as $publication) {
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
        return $publicationsStatus2;
    }
    public function mostPopularPublication(User $user)
    {
        // De tous les récits publiés (status 2) avec au moins un chapitre publié (status 2), on récupère le plus populaire (pop)
        $mostPopularPublication = null;
        $highestPop = -1;

        foreach ($user->getPublications() as $publication) {
            if ($publication->getStatus() == 2) {
                $hasChapterStatus2 = false;
                foreach ($publication->getPublicationChapters() as $chapter) {
                    if ($chapter->getStatus() == 2) {
                        $hasChapterStatus2 = true;
                        break;
                    }
                }

                if ($hasChapterStatus2) {
                    $currentPop = $publication->getPop();
                    if ($currentPop > $highestPop) {
                        $highestPop = $currentPop;
                        $mostPopularPublication = $publication;
                    }
                }
            }
        }
        return $mostPopularPublication;
    }
}
