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

    public function getSuggestedRecipes(string $ingredients): string
    {
        $open_ai_key = $this->parameterBag->get('OPENAI_AI_KEY');
        $open_ai = new OpenAi($open_ai_key);

        $prompt = "En tant que expert de la cuisine, crée une recette de cuisine détaillée avec titre, instructions étape par étape, temps de préparation et de cuisson, en utilisant ces ingrédients : " . $ingredients . ". Si certains ingrédients ne se marient pas bien ensemble, suggère des alternatives ou des compléments pour créer un plat équilibré et délicieux.";

        $response = $open_ai->chat([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => $prompt]
            ],
            'temperature' => 0.5,
            'max_tokens' => 3500,
            'frequency_penalty' => 0.5,
            'presence_penalty' => 0,
        ]);

        $json = json_decode($response, true);

        if ($json === null) {
            return 'Une erreur est survenue lors de l\'interprétation de la réponse de l\'API.';
        }

        if (isset($json['error'])) {
            return 'Une erreur est survenue lors du traitement de votre demande.';
        }

        if (isset($json['choices'][0]['message']) && is_array($json['choices'][0]['message'])) {
            $text = $json['choices'][0]['message']['content'] ?? 'Réponse non disponible';
            // Formatage supplémentaire si nécessaire
            return $this->formatRecipeResponse($text);
        }

        return 'La réponse de l\'API ne correspond pas au format attendu.';
    }

    private function formatRecipeResponse(string $response): string
    {
        // Divise la réponse en sections basées sur des mots-clés
        $title = $this->extractSection($response, 'Titre :', 'Temps de préparation :');
        $prepTime = $this->extractSection($response, 'Temps de préparation :', 'Temps de cuisson :');
        $cookTime = $this->extractSection($response, 'Temps de cuisson :', 'Ingrédients :');
        $ingredients = $this->extractSection($response, 'Ingrédients :', 'Instructions :');
        $instructions = $this->extractSection($response, 'Instructions :');

        // Construit la réponse formatée avec des sauts de ligne
        $formattedResponse = $title . "\n";
        $formattedResponse .= "Temps de préparation: " . $prepTime . "\n";
        $formattedResponse .= "Temps de cuisson: " . $cookTime . "\n\n";
        $formattedResponse .= "Ingrédients\n" . $ingredients . "\n\n";
        $formattedResponse .= "Instructions\n" . $instructions;

        return $formattedResponse;
    }

    private function extractSection(string $response, string $start, string $end = null): string
    {
        $startPos = strpos($response, $start);
        if ($startPos === false) {
            return 'Information non disponible';
        }

        $startPos += strlen($start);
        $endPos = $end ? strpos($response, $end, $startPos) : strlen($response);

        if ($endPos === false) {
            $endPos = strlen($response);
        }

        $section = trim(substr($response, $startPos, $endPos - $startPos));

        // Vérifier si la section est vide
        if (empty($section)) {
            return 'Information non disponible';
        }

        return $section;
    }
}
