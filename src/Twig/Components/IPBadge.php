<?php

namespace App\Twig\Components;

use IPTools\IP;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class IPBadge
{
    public IP $ip;
}
