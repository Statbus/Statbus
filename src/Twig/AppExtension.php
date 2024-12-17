<?php

namespace App\Twig;

use App\Twig\Extension\EnumExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('enum', [EnumExtension::class, 'createProxy'])
        ];
    }
}
