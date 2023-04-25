<?php


namespace App\Twig;

use DateTime;
use Exception;
use Twig\TwigFilter;
use DateTimeInterface;
use Twig\Extension\AbstractExtension;

class TwigDate extends AbstractExtension
{
    public function getFilters()
    {
        return [new TwigFilter("since", [$this, "sinceFilter"])];
    }
    public function sinceFilter($date): string
    {
        // Vérifie si la date n'est pas une instance de DateTimeInterface
        if (!$date instanceof DateTimeInterface) {
            // Si ce n'est pas le cas, essayez de convertir $date en un objet DateTime
            try {
                $date = new DateTime($date);
            } catch (Exception $e) {
                // Si la conversion échoue, retournez une chaîne vide ou un message d'erreur approprié
                return '';
            }
        }

        $dateTime1 = new DateTime("now");
        $dateTime2 = $date;
        $date = $dateTime1->diff($dateTime2);
        if ($date->format("%y") == 1) {
            $date = $date->format("%y an");
        } elseif ($date->format("%y") > 1) {
            $date = $date->format("%y ans");
        } elseif ($date->format("%m") > 0) {
            $date = $date->format("%m mois");
        } elseif ($date->format("%d") == 1) {
            $date = $date->format("%d jour");
        } elseif ($date->format("%d") > 1) {
            $date = $date->format("%d jours");
        } elseif ($date->format("%h") == 1) {
            $date = $date->format("%h heure");
        } elseif ($date->format("%h") > 1) {
            $date = $date->format("%h heures");
        } elseif ($date->format("%i") > 4) {
            $date = $date->format("%i minutes");
        } else {
            $date = "quelques instants";
        }
        return $date;
    }
}
