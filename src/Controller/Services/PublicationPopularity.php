<?php

namespace App\Controller\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationChapterLikeRepository;
use App\Repository\PublicationChapterViewRepository;
use App\Repository\PublicationChapterCommentRepository;
use App\Repository\PublicationChapterBookmarkRepository;

class PublicationPopularity
{
    private $pRepo;
    private $pchRepo;
    private $pchcRepo;
    private $pcvRepo;
    private $pclRepo;
    private $pcbRepo;

    private $em;

    public function __construct(EntityManagerInterface $em, PublicationRepository $pRepo, PublicationChapterRepository $pchRepo, PublicationChapterCommentRepository $pchcRepo, PublicationChapterViewRepository $pcvRepo, PublicationChapterLikeRepository $pclRepo, PublicationChapterBookmarkRepository $pcbRepo)
    {
        $this->pRepo = $pRepo;
        $this->pchRepo = $pchRepo;
        $this->pchcRepo = $pchcRepo;
        $this->pcvRepo = $pcvRepo;
        $this->pclRepo = $pclRepo;
        $this->pcbRepo = $pcbRepo;
        $this->em = $em;
    }
    /**
     * @param $publication
     * Cette fonction permet de calculer la popularité d'une publication
     * @return void
     */
    public function PublicationPopularity($publication)
    {
        $priorityPcv = 0.01; // views
        $priorityPcl = 0.06; // likes
        $priorityPchc = 0.04; // comments
        $priorityBm = 0.07; // Bookmarks

        $p = $this->pRepo->find($publication);
        $pch = $this->pchRepo->findBy(["publication" => $p]);
        $pchc = count($this->pchcRepo->findBy(["chapter" => $pch]));
        $pcv = count($this->pcvRepo->findBy(["chapter" => $pch]));
        $pcl = count($this->pclRepo->findBy(["chapter" => $pch]));
        $pcb = count($this->pcbRepo->findBy(["chapter" => $pch]));

        // Calculate time decay factor
        $createdAt = $p->getCreated();
        $timeSincePublication = ($createdAt !== null) ? (time() - strtotime($createdAt->format('Y-m-d H:i:s'))) : 0;
        $decayFactor = exp(- ($timeSincePublication / (60 * 60 * 24 * 30))); // factor de décroissance (exprimé en jours)

        // Calculate popularity with time decay factor
        $popularity = ($pcv * $priorityPcv) + ($pcl * $priorityPcl) + ($pchc * $priorityPchc) + ($pcb * $priorityBm);
        $popularity = $popularity * $decayFactor;

        // Update popularity in database
        $p->setPop($popularity);
        $this->em->persist($p);
        $this->em->flush();

        return;
    }
}
