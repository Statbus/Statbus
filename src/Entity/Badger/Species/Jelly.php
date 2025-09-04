<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;

#[SpeciesClass(name: 'Jelly')]
class Jelly extends Species
{
    public const SPRITE_PREFIX = 'jelly';
}
