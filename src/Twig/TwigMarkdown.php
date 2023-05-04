<?php
// src/Twig/MarkdownExtension.php

namespace App\Twig;

use Twig\TwigFilter;
use Michelf\Markdown;
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
        $markdownParser = new Markdown();

        return $markdownParser->defaultTransform($text);
    }
}
