<?php


namespace App\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class TwigTernary extends AbstractExtension
{
    public function getFilters()
    {
        return [new TwigFilter("ternary", [$this, "TwigTernary"])];
    }
    public function TwigTernary($value, $ifTrue, $ifFalse)
    {
        return $value ? $ifTrue : $ifFalse;
    }
}
