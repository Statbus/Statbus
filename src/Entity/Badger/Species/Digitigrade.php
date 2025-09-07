<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Enum\Badger\Directions;

#[SpeciesClass(name: 'Lizard (Digitigrade)')]
class Digitigrade extends Lizard
{
    public function getBodySprites(
        Directions $dir = Directions::SOUTH,
        ?string $gender = 'male'
    ): array {
        $sprites = parent::getBodySprites(
            dir: $dir,
            gender: $gender
        );
        $sprites['rLeg'] = str_replace(
            'lizard_r',
            'digitigrade_r',
            $sprites['rLeg']
        );
        $sprites['lLeg'] = str_replace(
            'lizard_l',
            'digitigrade_l',
            $sprites['lLeg']
        );
        return $sprites;
    }
}
