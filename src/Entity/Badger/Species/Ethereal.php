<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;
use App\Service\Icons\RenderDMI;

#[SpeciesClass(name: 'Ethereal')]
class Ethereal extends Species
{
    public string $path = '/mob/human/species/ethereal/bodyparts/';
    public string $prefix = 'ethereal';
    public bool $gendered = false;
    public bool $canColor = false;
}
