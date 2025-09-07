<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Enum\Badger\Directions;

#[SpeciesClass(name: 'Mushroom')]
class Mushroom extends Species
{
    public string $path = '/mob/human/bodyparts_greyscale';
    public string $prefix = 'mush';

    public function getBodySprites(
        Directions $dir = Directions::SOUTH,
        ?string $gender = 'male'
    ): array {
        $sprites = parent::getBodySprites(
            gender: $gender,
            dir: $dir
        );
        unset($sprites['lHand']);
        unset($sprites['rHand']);
        return $sprites;
    }
}
