<?php

namespace App\Twig\Components;

use App\Service\Roles\RoleDataService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class RoleBadge
{
    public $role;
}
