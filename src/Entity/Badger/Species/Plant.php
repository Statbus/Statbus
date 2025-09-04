<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;

#[SpeciesClass(name: 'Plant')]
class Plant extends Species
{
    public const SPRITE_PREFIX = 'plant';
}
