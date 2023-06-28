<?php

namespace App\Services;

use Orhanerday\OpenAi\OpenAi;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OpenAiAPIService
{
    public function __construct(
        private ParameterBagInterface $parameterBag
    ) {
    }
    public function getHistory(string $regex): string
    {

        $openai_key = $this->parameterBag->get('OPENAI_API_KEY');
        $openai = new OpenAi($openai_key);
        $opts = [
            'prompt' => "Mets-toi dans la peau d'un grand écrivain, avec un grand style (style Gustave Flaubert), et écris-moi le premier chapitre d'un roman sorti de ton imagination. Le temps de lecture doit être de deux minutes, et l'écriture doit être subtile.",
            'temperature' => 0.9,
            "max_tokens" => 150,
            "frequency_penalty" => 0,
            "presence_penalty" => 0.6,
            "stream" => true,
        ];
        $chat = $openai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    "role" => "system",
                    "content" => "Tu es le grand écrivain Gustave Flaubert"
                ],
                [
                    "role" => "user",
                    "content" => "Mets-toi dans la peau d'un grand écrivain, avec un grand style (style Gustave Flaubert), et écris-moi le premier chapitre d'un roman sorti de ton imagination. Le temps de lecture doit être de 5 minutes, et l'écriture doit être subtile. Enfin, tu ne dois faire aucune référence à Gustave Flaubert, sinon par son style d'écriture."
                ],
                // [
                //     "role" => "assistant",
                //     "content" => "The Los Angeles Dodgers won the World Series in 2020."
                // ],
                // [
                //     "role" => "user",
                //     "content" => "Where was it played?"
                // ],
            ],
            'temperature' => 1.0,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);
        $d = $chat;

        return $d;
    }
}
