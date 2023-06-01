<?php

namespace App\Services;

use Michelf\Markdown;
use Michelf\MarkdownExtra;



class SmileyMessage
{
    public function convertSmileyToEmoji(?string $text): string
    {
        if ($text === null) {
            return "";
        }
        $parser = new MarkdownExtra;
        $parser->hard_wrap = true;
        $text = $parser->transform($text);
        $text = str_replace('<a ', '<a target="_blank" rel="nofollow" ', $text);

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
