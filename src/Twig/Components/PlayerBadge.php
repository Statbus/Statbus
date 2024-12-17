<?php

namespace App\Twig\Components;

use App\Security\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PlayerBadge
{
    public User $player;
}
