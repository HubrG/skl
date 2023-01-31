<?php


namespace App\Twig;

use DateTime;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class TwigChapter extends AbstractExtension
{
    public function getFilters()
    {
        return [new TwigFilter("chapter", [$this, "infoChapter"])];
    }
    public function infoChapter($array, $type, $format): string
    {
        if ($type == "nbr") { // compter le nombre de chapitre publiés (status : 2)
            $nbr = 0;
            foreach ($array as $a) {
                if ($a->getStatus() == 2) {
                    $nbr++;
                }
            }
            if ($nbr > 0) {
                if ($format == "ft") {
                    return $nbr . " chapitre" . ($nbr > 1 ? "s" : "");
                } elseif ($format == "ft_short") {
                    return $nbr . " chap.";
                } else {
                    return $nbr;
                }
            } else {
                return "Aucun chapitre";
            }
        } elseif ($type == "rt") { // read time - cumul de tous les chapitres publiés
            $word = 0;
            foreach ($array as $a) {
                if ($a->getStatus() == 2) {
                    $word += str_word_count(strip_tags($a->getContent()));
                }
            }
            $minutes = floor($word / 200);
            $hours = floor($minutes / 60);
            $minutes = $minutes % 60;
            if ($format == "ft") {
                return $hours . "h" . ($minutes < 10 ? "0" . $minutes : $minutes);
            } elseif ($format == "ft_short") {
                return $hours . "h" . ($minutes < 10 ? "0" . $minutes : $minutes);
            } else {
                return $hours . "h" . ($minutes < 10 ? "0" . $minutes : $minutes);
            }
        }
        return null;
    }
}
