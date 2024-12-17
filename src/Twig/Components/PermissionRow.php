<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PermissionRow
{
    public string $ckey;
    public ?int $permissions = 0;
}
