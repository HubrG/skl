<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\PublicationChapterRepository;


class WordCount
{

    public function __construct(private PublicationRepository $pRepo, private PublicationChapterRepository $pcRepo, private EntityManagerInterface $em)
    {
    }
    /**
     * @param $idChap
     * 
     * Cette fonction compte le nombre de mots dans une chaîne de caractères pour un chapitre. Elle fait appelle, ensuite, à la méthode WordCountPublication pour mettre à jour le word_count de la publication.
     * 
     * @return int
     */
    public function WordCountChapter($idChap): void
    {
        $chapter = $this->pcRepo->find($idChap);
        $text = strip_tags($chapter->getContent());
        $wordCount = str_word_count($text);
        // On met à jour le word_count du chapitre dans la BDD
        $chapter->setWordCount($wordCount);
        $this->em->persist($chapter);
        $this->em->flush();
        // On appelle la méthode WordCountPublication pour mettre à jour le word_count de la publication
        $this->WordCountPublication($chapter->getPublication()->getId());
    }
    public function WordCountPublication($idPub): void
    {
        $publication = $this->pRepo->find($idPub);
        $wordCount = 0;
        foreach ($publication->getPublicationChapters() as $chapter) {
            if ($chapter->getStatus() == 2) {
                $wordCount += $chapter->getWordCount();
            }
        }
        // On met à jour le word_count de la publication dans la BDD
        $publication->setWordCount($wordCount);
        $this->em->persist($publication);
        $this->em->flush();
    }
    public function WordCountInit($idPub): void
    {
        // On recherche tous les chapitres publiés (status = 2) de la publication
        $qb = $this->pcRepo->createQueryBuilder('pc')
            ->where('pc.publication = :idPub')
            ->andWhere('pc.status > 0')
            ->setParameter('idPub', $idPub);
        $chapters = $qb->getQuery()->getResult();
        // On compte le nombre de mots de chaque chapitre avec Striptags et Str_word_count
        foreach ($chapters as $chapter) {
            $text = strip_tags($chapter->getContent());
            $wordCount = str_word_count($text);
            // On met à jour le word_count du chapitre dans la BDD
            $chapter->setWordCount($wordCount);
            $this->em->persist($chapter);
            $this->em->flush();
        }
        // On appelle la méthode WordCountPublication pour mettre à jour le word_count de la publication
        $this->WordCountPublication($idPub);
    }
}
