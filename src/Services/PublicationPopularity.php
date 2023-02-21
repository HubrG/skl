<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationDownloadRepository;
use App\Repository\PublicationPopularityRepository;
use App\Repository\PublicationChapterLikeRepository;
use App\Repository\PublicationChapterViewRepository;
use App\Repository\PublicationChapterCommentRepository;
use App\Repository\PublicationChapterBookmarkRepository;
use App\Entity\PublicationPopularity as EntityPublicationPopularity;

class PublicationPopularity
{
    private $pRepo;
    private $pchRepo;
    private $pchcRepo;
    private $pcvRepo;
    private $pclRepo;
    private $pcbRepo;
    private $pdRepo;
    private $ppRepo;

    private $em;

    public function __construct(PublicationPopularityRepository $ppRepo, PublicationDownloadRepository $pdRepo, EntityManagerInterface $em, PublicationRepository $pRepo, PublicationChapterRepository $pchRepo, PublicationChapterCommentRepository $pchcRepo, PublicationChapterViewRepository $pcvRepo, PublicationChapterLikeRepository $pclRepo, PublicationChapterBookmarkRepository $pcbRepo)
    {
        $this->pRepo = $pRepo;
        $this->pchRepo = $pchRepo;
        $this->pchcRepo = $pchcRepo;
        $this->pcvRepo = $pcvRepo;
        $this->pclRepo = $pclRepo;
        $this->pcbRepo = $pcbRepo;
        $this->em = $em;
        $this->pdRepo = $pdRepo;
        $this->ppRepo = $ppRepo;
    }
    /**
     * @param $publication
     * Cette fonction permet de calculer la popularité d'une publication
     * @return void
     */
    public function PublicationPopularity($publication)
    {
        $priorityPcv = 0.01; // views
        $priorityPchc = 0.030; // comments
        $priorityPcl = 0.050; // likes
        $priorityDl = 0.060; // Downloads
        $priorityBm = 0.070; // Bookmarks

        $p = $this->pRepo->find($publication);
        $pch = $this->pchRepo->findBy(["publication" => $p, "status" => 2]);
        //
        $pchc = count($this->pchcRepo->findBy(["chapter" => $pch])); // comments
        $pcv = count($this->pcvRepo->findBy(["chapter" => $pch])); // views
        $pcl = count($this->pclRepo->findBy(["chapter" => $pch])); // likes
        $pcb = count($this->pcbRepo->findBy(["chapter" => $pch])); // bookmarks
        $pdl = count($this->pdRepo->findBy(["publication" => $p])); // downloads

        // Calculate time decay factor
        $createdAt = $p->getCreated();
        $timeSincePublication = ($createdAt !== null) ? (time() - strtotime($createdAt->format('Y-m-d H:i:s'))) : 0;
        $decayFactor = exp(($timeSincePublication / (60 * 60 * 24 * 30))); // factor de décroissance (exprimé en jours)

        // Calculate popularity with time decay factor
        $popularity = ($pcv * $priorityPcv) + ($pcl * $priorityPcl) + ($pchc * $priorityPchc) + ($pcb * $priorityBm) + ($pdl * $priorityDl);
        $popularity = $popularity * $decayFactor;

        // * Update popularity in "Publication" database
        $p->setPop($popularity);
        $this->em->persist($p);
        $this->em->flush();

        // * Update popularity in "PublicationPopularity" database
        // * Si la dernière valeur de popularité présente dans la bdd est date de 7 jours d'après la date immuable "CreatedAt", on ajoute une nouvelle valeur

        $pp = $this->ppRepo->findOneBy(["publication" => $p], ["createdAt" => "DESC"]);
        if ($pp !== null) {
            $createdAt = $pp->getCreatedAt();
            $timeSincePublication = ($createdAt !== null) ? (time() - strtotime($createdAt->format('Y-m-d H:i:s'))) : 0;
            if ($timeSincePublication > 302400) { // 3,5 jours
                $pp = new EntityPublicationPopularity;
                $pp->setPublication($p);
                $pp->setPopularity($popularity);
                $pp->setCreatedAt(new \DateTimeImmutable("now"));
                $this->em->persist($pp);
                $this->em->flush();
            }
        } else {
            $pp = new EntityPublicationPopularity;
            $pp->setPublication($p);
            $pp->setPopularity($popularity);
            $pp->setCreatedAt(new \DateTimeImmutable("now"));
            $this->em->persist($pp);
            $this->em->flush();
        }

        return;
    }
}
