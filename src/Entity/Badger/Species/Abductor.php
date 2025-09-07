<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;

#[SpeciesClass(name: 'Abductor')]
class Abductor extends Species
{
    public string $path = '/mob/human/bodyparts';
    public string $prefix = 'abductor';
    public bool $canColor = false;
    public bool $gendered = false;
}
