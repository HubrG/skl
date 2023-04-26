<?php
// src/Twig/ReadAllChaptersExtension.php

namespace App\Twig;

use App\Entity\User;
use Twig\TwigFilter;
use App\Repository\ChapterRepository;
use Twig\Extension\AbstractExtension;
use App\Repository\PublicationChapterRepository;

class TwigReadAllChaptersExtension extends AbstractExtension
{
    private $chapterRepository;

    public function __construct(PublicationChapterRepository $chapterRepository)
    {
        $this->chapterRepository = $chapterRepository;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('read_all_chapters', [$this, 'hasReadAllChapters']),
        ];
    }

    public function hasReadAllChapters(User $user, int $publicationId): bool
    {
        $readChaptersIds = [];

        foreach ($user->getPublicationReads() as $read) {
            if ($read->getChapter()->getPublication()->getId() === $publicationId && $read->getChapter()->getStatus() === 2) {
                $readChaptersIds[] = $read->getChapter()->getId();
            }
        }

        $publicationChapters = $this->chapterRepository->findBy(['publication' => $publicationId, 'status' => 2]);

        $publicationChaptersIds = array_map(function ($chapter) {
            return $chapter->getId();
        }, $publicationChapters);

        $difference = array_diff($publicationChaptersIds, $readChaptersIds);

        return count($difference) === 0;
    }
}
