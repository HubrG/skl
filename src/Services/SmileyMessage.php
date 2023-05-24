<?php

namespace App\Services;

use Michelf\Markdown;


class SmileyMessage
{
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
        $markdownParser = new Markdown();


        return str_replace(array_keys($smileyToEmojiMap), array_values($smileyToEmojiMap), $markdownParser->defaultTransform(nl2br($text)));
    }
}