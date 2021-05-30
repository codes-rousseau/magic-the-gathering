<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslateColorsExtension extends AbstractExtension
{
    private array $colorsMapping;

    public function __construct(array $colorsMapping)
    {
        $this->colorsMapping = $colorsMapping;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('translate_colors', [$this, 'translateColors']),
        ];
    }

    public function translateColors(string $colors): string
    {
        $length = strlen($colors);

        if ($length < 1) {
            return $colors;
        }

        if ($length === 1) {
            if (!empty($this->colorsMapping[$colors])) {
                return $this->colorsMapping[$colors];
            }

            return '';
        }

        $result = '';

        $splitColors = str_split($colors);

        for ($i = 0; $i < sizeof($splitColors); $i++) {
            if ($i === sizeof($splitColors) - 1) {
                $result .= $this->colorsMapping[$splitColors[$i]];
                continue;
            }
            $result .= $this->colorsMapping[$splitColors[$i]] . ', ';
        }

        return $result;
    }
}