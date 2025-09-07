<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;

#[SpeciesClass(name: 'Pod')]
class Pod extends Species
{
    public string $path = '/mob/human/bodyparts_greyscale';
    public string $prefix = 'pod';
}
