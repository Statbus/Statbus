<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;

#[SpeciesClass(name: 'Human')]
class Human extends Species
{
    public const SPRITE_PREFIX = 'human';
    public const SKINTONES = [
        '#ffe0d1',
        '#fcccb3',
        '#e8b59b',
        '#d9ae96',
        '#c79b8b',
        '#ffdeb3',
        '#e3ba84',
        '#c4915e',
        '#b87840',
        '#754523',
        '#471c18',
        '#fff4e6',
        '#ffc905'
    ];
}
