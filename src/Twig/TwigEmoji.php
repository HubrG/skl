<?php

namespace App\Twig;

use Twig\TwigFilter;
use Michelf\Markdown;
use Twig\Extension\AbstractExtension;

class TwigEmoji extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('smiley_to_emoji', [$this, 'convertSmileyToEmoji']),
        ];
    }

    public function convertSmileyToEmoji(string $text): string
    {
        $smileyToEmojiMap = [
            ':-)' => '🙂',
            ':)' => '🙂',
            ':-D' => '😃',
            ':D' => '😃',
            ':-(' => '🙁',
            ':(' => '🙁',
            ';-)' => '😉',
            ';)' => '😉',
            ':-P' => '😛',
            ':P' => '😛',
            ':-p' => '😛',
            ':p' => '😛',
            ':-O' => '😮',
            ':O' => '😮',
            ':-o' => '😮',
            ':o' => '😮',
            ':-*' => '😘',
            ':*' => '😘',
            '<3' => '❤️',
            ':/' => '😕',
            ':-/' => '😕',
            ':|' => '😐',
            ':-|' => '😐',
            ':X' => '🤐',
            ':-X' => '🤐',
            'B-)' => '😎',
            '8-)' => '😎',
            ':-$' => '🤑',
            ':$' => '🤑',
            ':\'(' => '😢',
            ':-[' => '🧛',
            ':[' => '🧛',
            ':-]' => '😁',
            ':]' => '😁',
            'O:)' => '😇',
            'o:)' => '😇',
            '>:)' => '😈',
            '>:(' => '😠',
            ':-@' => '😡',
            ':@' => '😡',
            ':-#' => '🤐',
            ':#' => '🤐',
            ':-&' => '🤐',
            ':&' => '🤐',
            ':-S' => '😖',
            ':S' => '😖',
            ':-s' => '😖',
            ':s' => '😖',
            ':-+' => '🤕',
            ':-Q' => '🤮',
            ':Q' => '🤮',
            ':-q' => '🤮',
            ':q' => '🤮',
            ':_:' => '😭',
            'T_T' => '😭',
            ':-J' => '😏',
            ':J' => '😏',
            ':-j' => '😏',
            ':j' => '😏',
            ':D-' => '😆',
            'XD' => '😆',
            ':-.' => '😐',
            ':.' => '😐',
        ];

        return str_replace(array_keys($smileyToEmojiMap), array_values($smileyToEmojiMap), $text);
    }
}
