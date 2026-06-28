<?php

namespace App\Core\Ai\Support;

final class AiPromptTemplateInterpolator
{
    /**
     * @param  array<string, string>  $variables
     */
    public static function interpolate(string $template, array $variables): string
    {
        $replacements = [];

        foreach ($variables as $key => $value) {
            $replacements['{{'.$key.'}}'] = $value;
        }

        $result = strtr($template, $replacements);

        return preg_replace('/\n{3,}/', "\n\n", trim($result)) ?? trim($result);
    }
}
