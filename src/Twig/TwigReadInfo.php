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
            return ($m < 10 ? "0" . $m : $m);
        } elseif ($type == "ri_sc") { // compteur de secondes
            return ($s < 10 ? "0" . $s : $s);
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
        } elseif ($type == "ft") { // full time en heure et minute (par rapport à un nombre total de minutes)
            $minutes = $string;
            $hours = floor($minutes / 60);
            $minutes = $minutes % 60;
            return $hours . "h" . ($minutes < 10 ? "0" . $minutes : $minutes);
        } elseif ($type == "nbr_page_A5") {
            $stringOrigin = strip_tags($stringOrigin);
            // on compte le nombre de caractères, espaces compris, de $stringOrigine
            $stringOrigin = \strlen($stringOrigin);
            $largeur_disponible = 9.8; // Largeur disponible pour le texte en cm
            $hauteur_disponible = 16; // Hauteur disponible pour le texte en cm
            $hauteur_police = 0.42; // Hauteur de la police en cm

            $caracteres_par_ligne = 35; // Nombre de caractères par ligne (estimation basée sur une police de caractère Times New Roman de 12 points avec un interligne de 1,5)
            $lignes_par_page = floor($hauteur_disponible / $hauteur_police); // Nombre de lignes par page

            $caracteres_par_page = $caracteres_par_ligne * $lignes_par_page; // Nombre de caractères par page
            return ceil($stringOrigin / $caracteres_par_page); // Nombre de pages (arrondi au nombre entier supérieur)
        } elseif ($type == "nbr_page_A4") {
            $stringOrigin = strip_tags($stringOrigin);
            // on compte le nombre de caractères, espaces compris, de $stringOrigine
            $stringOrigin = \strlen($stringOrigin);
            $largeur_disponible = 16; // Largeur disponible pour le texte en cm
            $hauteur_disponible = 24.7; // Hauteur disponible pour le texte en cm
            $hauteur_police = 0.42; // Hauteur de la police en cm

            $caracteres_par_ligne = 60; // Nombre de caractères par ligne (estimation basée sur une police de caractère Times New Roman de 12 points avec un interligne de 1,5)
            $lignes_par_page = floor($hauteur_disponible / $hauteur_police); // Nombre de lignes par page

            $caracteres_par_page = $caracteres_par_ligne * $lignes_par_page; // Nombre de caractères par page
            return ceil($stringOrigin / $caracteres_par_page); // Nombre de pages (arrondi au nombre entier supérieur)
        }
        return null;
    }
}
