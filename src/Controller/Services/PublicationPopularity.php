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
        $priorityPcv = 0.001; // views
        $priorityPcl = 0.006; // likes
        $priorityPchc = 0.004; // comments
        $priorityBm = 0.007; // Bookmarks
        $p = $this->pRepo->find($publication);
        $pch = $this->pchRepo->findBy(["publication" => $p]);
        $pchc = count($this->pchcRepo->findBy(["chapter" => $pch]));
        $pcv = count($this->pcvRepo->findBy(["chapter" => $pch]));
        $pcl = count($this->pclRepo->findBy(["chapter" => $pch]));
        $pcb = count($this->pcbRepo->findBy(["chapter" => $pch]));
        // On crée un tableau avec les publications et leur nombre de vue
        $popularity = ($pcv * $priorityPcv) + ($pcl * $priorityPcl) + ($pchc * $priorityPchc) + ($pcb * $priorityBm);
        // On met à jour en bdd
        $p = $this->pRepo->find($publication);

        $p->setPop($popularity);

        $this->em->persist($p);

        $this->em->flush();

        return;
    }
}
