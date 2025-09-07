<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;

#[SpeciesClass(name: 'Snail')]
class Snail extends Species
{
    public string $path = '/mob/human/bodyparts_greyscale';
    public string $prefix = 'snail';
    public bool $gendered = false;
}
