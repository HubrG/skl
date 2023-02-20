<?php

namespace App\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class TwigRegex extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter("regex_replace", [$this, "twigRegex"]),
        ];
    }

    public function twigRegex($value, $pattern, $replacement)
    {
        return preg_replace($pattern, $replacement, $value);
    }
}
