<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;

#[SpeciesClass(name: 'Jelly')]
class Jelly extends Species
{
    public string $path = '/mob/human/bodyparts_greyscale';
    public string $prefix = 'jelly';
}
