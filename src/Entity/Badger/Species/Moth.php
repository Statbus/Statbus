<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;
use App\Enum\Badger\Directions;

#[SpeciesClass(name: 'Moth')]
class Moth extends Species
{
    public string $path = '/mob/human/species/moth/bodyparts';
    public string $prefix = 'moth';
    public bool $canColor = false;

    public array $extraPaths = [
        'behindFront' => [
            'mothWings' => '/mob/human/species/moth/moth_wings/',
            'mothAntennae' => '/mob/human/species/moth/moth_antennae/'
        ],
        'body' => [
            'mothMarkings' => '/mob/human/species/moth/moth_markings/'
        ]
    ];

    public function getBodySprites(
        Directions $dir = Directions::SOUTH,
        ?string $gender = 'male'
    ): array {
        $sprites = parent::getBodySprites(
            gender: $gender,
            dir: $dir
        );
        $sprites['head'] = str_replace(['_m', '_f'], '', $sprites['head']);
        return $sprites;
    }
}
