<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Orhanerday\OpenAi\OpenAi;

class OpenAiService
{
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getHistory(string $regex): string
    {
        $open_ai_key = $this->parameterBag->get('OPENAI_AI_KEY');
        $open_ai = new OpenAi($open_ai_key);

        $complete = $open_ai->completion([
            'model' => 'text-davinci-003',
            'prompt' => 'Explique en français et sous forme d\'histoire la REGEX suivante : ' . $regex,
            'temperature' => 0,
            'max_tokens' => 3500,
            'frequency_penalty' => 0.5,
            'presence_penalty' => 0,
        ]);

        $json = json_decode($complete, true);

        if (isset($json['error'])) {
            $json = $json['choices'][0]['text'];

            return $json;
        }

        $json = 'Une erreur est survenue, Père Castor ne connait pas toutes les REGEX du monde';

        return $json;
    }
}
