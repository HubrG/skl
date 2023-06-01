<?php
// src/Twig/MarkdownExtension.php

namespace App\Twig;

use Twig\TwigFilter;
use Michelf\MarkdownExtra;
use Twig\Extension\AbstractExtension;

class TwigMarkdown extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', [$this, 'formatMarkdown'], ['is_safe' => ['html']]),
        ];
    }

    public function formatMarkdown(string $text): string
    {
        $parser = new MarkdownExtra;
        $parser->hard_wrap = true;
        $text = $parser->transform($text);

        $text = $parser->transform($text);
        return str_replace('<a ', '<a data-turbo-frame="_top" rel="nofollow" ', $text);
    }
}
