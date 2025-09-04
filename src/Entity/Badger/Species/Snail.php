<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;

#[SpeciesClass(name: 'Snail')]
class Snail extends Species
{
    public const SPRITE_PREFIX = 'snail';

    public bool $gendered = false;
}
