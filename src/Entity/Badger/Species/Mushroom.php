<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Enum\Badger\Directions;

#[SpeciesClass(name: 'Mushroom')]
class Mushroom extends Species
{
    public const SPRITE_PREFIX = 'mush';

    public function getSpriteIcons(
        string $gender = 'male',
        Directions $dir = Directions::SOUTH
    ): array {
        $sprites = parent::getSpriteIcons($gender, $dir);
        unset($sprites['lHand']);
        unset($sprites['rHand']);
        return $sprites;
    }
}
