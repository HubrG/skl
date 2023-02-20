<?php


namespace App\Twig;

use DateTime;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class TwigContains extends AbstractExtension
{
    public function getFilters()
    {
        return [new TwigFilter("contains", [$this, "containsFilter"])];
    }
    public function containsFilter($haystack, $needle)
    {
        return in_array($needle, $haystack);
    }
}
