<?php

namespace App\Twig\Extension;

use Twig\Attribute\AsTwigFilter;
use Twig\Environment;

class LinkifyCkeysExtension
{
    #[AsTwigFilter('link_ckeys', needsEnvironment: true, isSafe: ['html'])]
    public function linkCkeysInText(Environment $env, string $text): string
    {
        return preg_replace_callback(
            '/([a-zA-Z0-9@]+)\/\(([^)]+)\)/',
            function ($matches) use ($env) {
                $ckey = $matches[1];
                $name = $matches[2];
                return (
                    $env->render('components/PlayerLink.html.twig', [
                        'ckey' => $ckey
                    ]) .
                    '/(' .
                    htmlspecialchars($name, ENT_QUOTES) .
                    ')'
                );
            },
            $text
        );
    }
}
