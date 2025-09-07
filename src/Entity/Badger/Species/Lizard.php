<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Enum\Badger\Directions;

#[SpeciesClass(name: 'Lizard')]
class Lizard extends Species
{
    public string $path = '/mob/human/species/lizard/bodyparts';
    public string $prefix = 'lizard';

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
