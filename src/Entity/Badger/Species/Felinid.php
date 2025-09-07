<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;

#[SpeciesClass(name: 'Felinid')]
class Felinid extends Human
{
    public array $extraPaths = [
        'behindFront' => [
            'felinidTail' => '/mob/human/cat_features'
        ],
        'body' => []
    ];
}
