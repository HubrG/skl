<?php


namespace App\Twig;

use DateTime;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class TwigDate extends AbstractExtension
{
    public function getFilters()
    {
        return [new TwigFilter("since", [$this, "sinceFilter"])];
    }
    public function sinceFilter($date): string
    {
        $dateTime1 =  new DateTime("now");
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
