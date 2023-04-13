<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationCommentRepository;
use App\Repository\PublicationBookmarkRepository;
use App\Repository\PublicationDownloadRepository;
use App\Repository\PublicationPopularityRepository;
use App\Repository\PublicationChapterLikeRepository;
use App\Repository\PublicationChapterViewRepository;
use App\Entity\PublicationPopularity as EntityPublicationPopularity;

class PublicationPopularity
{
    private $pRepo;
    private $pchRepo;

    private $pcvRepo;
    private $pcomRepo;
    private $pclRepo;
    private $pbRepo;
    private $pdRepo;
    private $ppRepo;

    private $em;

    public function __construct(PublicationCommentRepository $pcomRepo, PublicationPopularityRepository $ppRepo, PublicationDownloadRepository $pdRepo, EntityManagerInterface $em, PublicationRepository $pRepo, PublicationChapterRepository $pchRepo,  PublicationChapterViewRepository $pcvRepo, PublicationChapterLikeRepository $pclRepo, PublicationBookmarkRepository $pbRepo)
    {
        $this->pRepo = $pRepo;
        $this->pchRepo = $pchRepo;
        $this->pcvRepo = $pcvRepo;
        $this->pclRepo = $pclRepo;
        $this->pbRepo = $pbRepo;
        $this->em = $em;
        $this->pdRepo = $pdRepo;
        $this->ppRepo = $ppRepo;
        $this->pcomRepo = $pcomRepo;
    }
    /**
     * @param $publication
     * type
     * 
     * Cette fonction calcule la popularité d'une publication à partir des données relatives à la publication et de certaines de ses caractéristiques telles que le nombre de vues, de mentions "j'aime", de téléchargements, de signets et de commentaires.
     * 
     * La popularité est calculée en utilisant un facteur de décroissance basé sur le temps écoulé depuis la date de publication. Plus la date de publication est éloignée, plus la valeur de la popularité est réduite.
     * 
     * La popularité est ensuite mise à jour dans la base de données de la publication ainsi que dans une base de données séparée pour stocker l'historique de popularité. Si la dernière valeur de popularité dans la base de données de l'historique de popularité date de plus de 3,5 jours, une nouvelle entrée est créée avec la popularité mise à jour. Sinon, l'entrée existante est mise à jour avec la nouvelle valeur de popularité.
     * 
     * @return void
     */
    public function PublicationPopularity($publication)
    {
        $priorityPcv = 0.01; // views chapter
        $priorityPcl = 0.050; // likes chapter
        $priorityDl = 0.060; // Downloads chapter
        $priorityBmC = 0.060; // Bookmarks chapter
        $priorityBm = 0.070; // Bookmarks publication
        $priorityPcom = 0.030; // Comments publication

        $p = $this->pRepo->find($publication);
        $pch = $this->pchRepo->findBy(["publication" => $p, "status" => 2]);
        $totalChapters = count($pch);
        //
        $pcv = count($this->pcvRepo->findBy(["chapter" => $pch])); // views chapter
        $pcl = count($this->pclRepo->findBy(["chapter" => $pch])); // likes chapter
        $pcb = count($this->pbRepo->findBy(["chapter" => $pch])); // bookmarks chapter
        $pb = count($this->pbRepo->findBy(["publication" => $p])); // bookmarks publication
        $pdl = count($this->pdRepo->findBy(["publication" => $p])); // downloads chapter
        $pcom = count($this->pcomRepo->findBy(["publication" => $p])); // comments publication

        // Calculate time decay factor
        $publishedAt = $p->getPublishedDate();
        $timeSincePublication = ($publishedAt !== null) ? (time() - strtotime($publishedAt->format('Y-m-d H:i:s'))) : 0;
        $decayFactor = exp(($timeSincePublication / (60 * 60 * 24 * 15))); // factor de décroissance (exprimé en jours)

        // Calculate popularity with time decay factor
        $popularity = ($pcv * $priorityPcv) + ($pcom * $priorityPcom) + ($pcl * $priorityPcl)  + ($pcb * $priorityBmC)  + ($pb * $priorityBm) + ($pdl * $priorityDl);
        $popularity = $popularity * $decayFactor;

        // Calculate average popularity per chapter
        if ($totalChapters > 0) {
            $averagePopularityPerChapter = $popularity / $totalChapters;
        } else {
            $averagePopularityPerChapter = 0;
        }

        // * Update popularity in "Publication" database
        $p->setPop($averagePopularityPerChapter);
        $this->em->persist($p);
        $this->em->flush();

        // * Update popularity in "PublicationPopularity" database
        // * Si la dernière valeur de popularité présente dans la bdd est date de 7 jours d'après la date immuable "publishedAt", on ajoute une nouvelle valeur

        $pp = $this->ppRepo->findOneBy(["publication" => $p], ["createdAt" => "DESC"]);
        if ($pp !== null) {
            $publishedAt = $pp->getCreatedAt();
            $timeSincePublication = ($publishedAt !== null) ? (time() - strtotime($publishedAt->format('Y-m-d H:i:s'))) : 0;
            if ($timeSincePublication > 302400) { // 3,5 jours
                $pp = new EntityPublicationPopularity;
                $pp->setPublication($p);
                $pp->setPopularity($averagePopularityPerChapter);
                $pp->setCreatedAt(new \DateTimeImmutable("now"));
                $this->em->persist($pp);
                $this->em->flush();
            }
        } else {
            $pp = new EntityPublicationPopularity;
            $pp->setPublication($p);
            $pp->setPopularity($averagePopularityPerChapter);
            $pp->setCreatedAt(new \DateTimeImmutable("now"));
            $this->em->persist($pp);
            $this->em->flush();
        }

        return;
    }
}
