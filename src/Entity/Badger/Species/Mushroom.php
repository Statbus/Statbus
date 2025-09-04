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
        $sprites = [
            'rArm' =>

                    static::PARTS_DIR .
                    static::SPRITE_PREFIX .
                    '_r_arm-' .
                    $dir->value .
                    '.png'
                ,
            'lArm' =>

                    static::PARTS_DIR .
                    static::SPRITE_PREFIX .
                    '_l_arm-' .
                    $dir->value .
                    '.png'
                ,
            'lLeg' =>

                    static::PARTS_DIR .
                    static::SPRITE_PREFIX .
                    '_l_leg-' .
                    $dir->value .
                    '.png'
                ,
            'rLeg' =>

                    static::PARTS_DIR .
                    static::SPRITE_PREFIX .
                    '_r_leg-' .
                    $dir->value .
                    '.png'

        ];
        if ($gender == 'male') {
            $sprites['head'] =
                static::PARTS_DIR .
                static::SPRITE_PREFIX .
                '_head_m-' .
                $dir->value .
                '.png';
            $sprites['chest'] =
                static::PARTS_DIR .
                static::SPRITE_PREFIX .
                '_chest_m-' .
                $dir->value .
                '.png';
        } else {
            $sprites['head'] =
                static::PARTS_DIR .
                static::SPRITE_PREFIX .
                '_head_f-' .
                $dir->value .
                '.png';
            $sprites['chest'] =
                static::PARTS_DIR .
                static::SPRITE_PREFIX .
                '_chest_f-' .
                $dir->value .
                '.png';
        }
        return array_reverse($sprites);
    }
}
