<?php


namespace App\Twig;

use DateTime;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class TwigReadInfo extends AbstractExtension
{
    public function getFilters()
    {
        return [new TwigFilter("readInfo", [$this, "readInfoFilter"])];
    }
    public function readInfoFilter($string, $type): string
    {
        $stringOrigin = trim($string);
        $word = str_word_count(strip_tags($string));
        $m = floor($word / 200);
        $s = floor($word % 202 / (202 / 60));
        if ($type == "ri_mn") { // compteur de minutes
            return $m;
        } elseif ($type == "ri_sc") { // compteur de secondes
            return $s;
        } elseif ($type == "ri_wc") { // compteur de mots
            return $word;
        } elseif ($type == "ri_cc") { // compteur de caractères
            return \strlen(strip_tags(str_replace(" ", "", $string)));
        } elseif ($type == "ri_pc") { // compteur de paragaphes
            return \substr_count($stringOrigin, "<p");
        } elseif ($type == "ri_mn_ft") { // compteur de minute, full text
            return $m . " minute" . ($m > 1 ? "s" : "");
        } elseif ($type == "ri_sc_ft") { // compteur de secondes, full text
            return $s . " seconde" . ($s > 1 ? "s" : "");
        } elseif ($type == "ri_mn_ft_short") { // compteur de minute, full text raccourci
            return $m . " mn";
        } elseif ($type == "ri_sc_ft_short") { // compteur de secondes, full text raccourci
            return $s . " sc";
        }
        return null;
    }
}
