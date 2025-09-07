<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;

#[SpeciesClass(name: 'Plasma')]
class Plasma extends Species
{
    public string $path = '/mob/human/species/plasmaman/bodyparts';
    public string $prefix = 'plasmaman';
    public bool $gendered = false;
    public bool $canColor = false;
}
