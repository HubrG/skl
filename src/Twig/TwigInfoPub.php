<?php


namespace App\Twig;

use DateTime;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use App\Entity\PublicationChapterView;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationDownloadRepository;
use App\Repository\PublicationChapterLikeRepository;
use App\Repository\PublicationChapterViewRepository;
use App\Repository\PublicationChapterCommentRepository;
use App\Repository\PublicationChapterBookmarkRepository;

class TwigInfoPub extends AbstractExtension
{
	// private $em;
	// private $pLikeRepo;
	// private $pCommentRepo;
	// private $pBmRepo;
	// private $pViewRepo;
	// private $pDlRepo;

	// private $pRepo;
	// private $pchRepo;

	// public function __construct(EntityManagerInterface $em, PublicationChapterRepository $pchRepo, PublicationRepository $pRepo, PublicationChapterLikeRepository $pLikeRepo, PublicationChapterCommentRepository $pCommentRepo, PublicationChapterBookmarkRepository $pBmRepo, PublicationChapterViewRepository $pViewRepo, PublicationDownloadRepository $pDlRepo)
	// {
	// 	$this->em = $em;
	// 	$this->pLikeRepo = $pLikeRepo;
	// 	$this->pCommentRepo = $pCommentRepo;
	// 	$this->pBmRepo = $pBmRepo;
	// 	$this->pViewRepo = $pViewRepo;
	// 	$this->pDlRepo = $pDlRepo;
	// 	$this->pRepo = $pRepo;
	// 	$this->pchRepo = $pchRepo;
	// }
	// public function getFilters()
	// {
	// 	return [new TwigFilter("infopub", [$this, "infoPubFilter"])];
	// }
	// public function infoPubFilter($pub, $type): int
	// {
	// 	// On recherche les chapitres de la publications
	// 	// On compte le nombre de like pour chaque chapitre
	// 	if ($type == "like") {
	// 		$pchRepo = $this->pchRepo->findBy(["publication" => $pub]);
	// 		$nbLike = 0;
	// 		foreach ($pchRepo as $chap) {
	// 			$nbLike += $this->pLikeRepo->count(["chapter" => $chap]);
	// 		}
	// 		return $nbLike;
	// 	}
	// 	// On compte le nombre de commentaires pour chaque chapitre
	// 	if ($type == "comment") {
	// 		$pchRepo = $this->pchRepo->findBy(["publication" => $pub]);
	// 		$nbComment = 0;
	// 		foreach ($pchRepo as $chap) {
	// 			$nbComment += $this->pCommentRepo->count(["chapter" => $chap]);
	// 		}
	// 		return $nbComment;
	// 	}
	// 	// On compte le nombre de bookmark pour chaque chapitre
	// 	if ($type == "bookmark") {
	// 		$pchRepo = $this->pchRepo->findBy(["publication" => $pub]);
	// 		$nbBm = 0;
	// 		foreach ($pchRepo as $chap) {
	// 			$nbBm += $this->pBmRepo->count(["chapter" => $chap]);
	// 		}
	// 		return $nbBm;
	// 	}
	// 	// On compte le nombre de vue pour chaque chapitre
	// 	if ($type == "view") {
	// 		$pchRepo = $this->pchRepo->findBy(["publication" => $pub]);
	// 		$nbView = 0;
	// 		foreach ($pchRepo as $chap) {
	// 			$nbView += $this->pViewRepo->count(["chapter" => $chap]);
	// 		}
	// 		return $nbView;
	// 	}
	// 	// On compte le nombre de téléchargement pour chaque publication
	// 	if ($type == "download") {
	// 		return $this->pDlRepo->count(["publication" => $pub]);
	// 	}

	// 	return 0;
	// }
}
