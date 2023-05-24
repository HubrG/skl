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

    public function convertSmileyToEmoji(?string $text): string
    {
        if ($text === null) {
            return "";
        }

        $smileyToEmojiMap = [
            'xD' => '😆',
            'XD' => '😆',
            'x-D' => '😆',
            'X-D' => '😆',
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
            ':-.' => '😐',
            ':.' => '😐',
            ':-,' => '😐',
            ':,' => '😐',
            ':-<' => '😒',
            ':<' => '😒',
            ':->' => '😏',
            ':>' => '😏',
            ':-0' => '😮',
            ':0' => '😮',
            ':-()' => '😮',
            ':-[]' => '😮',
            ':-{}' => '😮',

        ];
        uksort($smileyToEmojiMap, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        $smileyRegex = '/(?<=\s|^)(' . implode('|', array_map(function ($smiley) {
            return preg_quote($smiley, '/');
        }, array_keys($smileyToEmojiMap))) . ')(?=\s|$)/';

        return preg_replace_callback($smileyRegex, function ($matches) use ($smileyToEmojiMap) {
            return $smileyToEmojiMap[$matches[0]];
        }, $text);
    }
}
