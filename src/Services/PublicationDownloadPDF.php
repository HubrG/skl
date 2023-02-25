<?php

namespace App\Services;

use TCPDF;
use DateTime;
use Exception;
use DateTimeImmutable;
use App\Entity\PublicationDownload;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PublicationChapterRepository;
use App\Repository\PublicationDownloadRepository;
use App\Repository\PublicationChapterCommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicationDownloadPDF extends AbstractController
{
    private $pRepo;
    private $pchRepo;
    private $em;

    private $pdRepo;

    public function __construct(PublicationDownloadRepository $pdRepo, EntityManagerInterface $em, PublicationRepository $pRepo, PublicationChapterRepository $pchRepo)
    {
        $this->pRepo = $pRepo;
        $this->pchRepo = $pchRepo;
        $this->em = $em;
        $this->pdRepo = $pdRepo;
    }
    /**
     * @param $publication
     * Cette fonction permet de calculer la popularité d'une publication
     * @return void
     */
    public function PublicationDownloadPDF($id)
    {
        $publication = $this->pRepo->find($id);
        $orderChap = $this->pchRepo->findBy(["publication" => $publication, "status" => 2], ["order_display" => "ASC"]);
        // On récupère tous le contenu des chapitres de la publication et on les assemble
        // On boucle les résultats de la requête
        $pdf = new TCPDF();
        $pdf->SetPrintHeader(false);
        $pdf->SetFont('Times', '', 12);
        // Définition de la taille de la page
        $pdf->AddPage();

        // Définition de la hauteur de la page et de la moitié haute
        $page_height = $pdf->getPageHeight();
        $half_height = $page_height / 2;

        // Définition du contenu HTML
        $html_top = '
		<div style="height:50%; display: flex; justify-content: center; align-items: center;">
        <div style="text-align:center;">
        <p></p><p></p><p></p><h1 style="font-size:2em">' . $publication->getTitle() . '</h1>
        <p style="font-size:1.2em">' . $publication->getUser()->getNickname() . '</p>
        <p><a href="http://scrilab.com/user/' . $publication->getUser()->getUsername() . '">Cliquez ici pour contacter l\'auteur(ice)</a></p>
        </div>
		</div>';
        // Ajout de la moitié haute
        $pdf->writeHTMLCell(0, $half_height, 0, 0, $html_top, 0, 0, false, true, 'C', true);
        if ($publication->getCover() != null) {
            $image_file = $publication->getCover();
            $html_bottom = '<div style="text-align:center"><img src="' . $image_file . '" width="' . $pdf->getPageWidth() . '"></div>';
            $pdf->writeHTMLCell(0, $half_height, 0, $half_height, $html_bottom, 0, 0, false, true, 'C', true);
        }
        // Ajout de la moitié basse
        foreach ($orderChap as $chapter) {
            // On vérifie que le chapitre est publié

            if ($chapter->getStatus() == 2) {
                $pdf->SetMargins(25, 25, 25);
                $html = $chapter->getContent();

                $regex = '/(<[^>]+) style=".*?"/i';

                // Remplacement
                $html = preg_replace($regex, '$1', $html);
                $html = str_replace("<p><br></p><p><br></p>", "", $html);
                $tagvs = array(
                    'p' => array(0 => array('n' => 0, 'h' => 1), 1 => array('n' => 1, 'h' => 1))
                );
                $pdf->setHtmlVSpace($tagvs);
                $html = '
				<style>
					.ql-align-center {
						text-align: center;
                      
					}
					  .ql-align-right {
						text-align: right;
                    
					  }
					  .ql-align-left {
						text-align: left;
                    
					  }
					  .ql-align-justify {
						text-align: justify;
                        
					  }
                p {
                font-size:12pt;
                line-height: 1.5;
               
                text-indent: 3em;
                margin: 0;
                padding: 0;
                }
					
				</style>
				' . $html;
                // Regex pour supprimer tous les attributs "style" des balises HTML


                $pdf->AddPage("p", "A4");
                $pdf->writeHTML(

                    "<div style='text-align:center'><h2>" . $chapter->getTitle() . "</h2></div>",
                    false,
                    false,
                    false,
                    false,
                    'C'
                );

                $pdf->writeHTML(

                    $html,
                    true,
                    false,
                    true,
                    false,
                    'J'
                );
            }
        }
        $pdf->SetAuthor($publication->getUser()->getNickname());
        $pdf->SetTitle($publication->getTitle());
        $pdf->SetSubject($publication->getCategory()->getName() . " - " . $publication->getSummary());
        // On récupère tous les keuwords de la publication
        $keywords = array();
        $pubKw = $publication->getPublicationKeywords();
        foreach ($pubKw as $k) {
            $keywords[] = $k->getKeyword();
        }
        $pdf->SetKeywords(implode(", ", $keywords));
        $pdf->SetCreator('http://scrilab.com');
        try {
            $this->addPublicationDownload($publication);
            $pdf->Output($publication->getTitle() . ' - ' . $publication->getUser()->getNickname() . '.pdf', 'D');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        // * On ajoute le téléchargement à la la BDD
    }
    private function addPublicationDownload($publication)
    {
        $pdRepo = $this->pdRepo;
        $pdRepo = $pdRepo->findOneBy(["publication" => $publication, "user" => $this->getUser()]);
        if ($pdRepo == null && $this->getUser() != $publication->getUser()) {
            $pd = new PublicationDownload();
            $pd->setPublication($publication);
            $pd->setUser($this->getUser());
            $pd->setDlAt(new DateTimeImmutable());
            $this->em->persist($pd);
            $this->em->flush();
        }
    }
}
