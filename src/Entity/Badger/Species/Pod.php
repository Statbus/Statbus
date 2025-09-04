<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;

#[SpeciesClass(name: 'Pod')]
class Pod extends Species
{
    public const SPRITE_PREFIX = 'pod';
}
