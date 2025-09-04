<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Enum\Badger\Directions;
use App\Service\Icons\RenderDMI;
use Symfony\Component\Filesystem\Path;

#[SpeciesClass(name: 'Lizard')]
class Lizard extends Species
{
    public const SPRITE_PREFIX = 'lizard';

    private string $partsDir;

    public function __construct(
        private RenderDMI $renderDMI
    ) {
        $this->partsDir =
            $renderDMI->getOutputDir() . '/mob/human/species/lizard/bodyparts';
    }

    public function getSpriteIcons(
        string $gender = 'male',
        Directions $dir = Directions::SOUTH
    ): array {
        $sprites = [
            'rArm' =>

                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_r_arm-' .
                    $dir->value .
                    '.png'
                ,
            'lArm' =>

                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_l_arm-' .
                    $dir->value .
                    '.png'
                ,
            'lLeg' =>

                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_l_leg-' .
                    $dir->value .
                    '.png'
                ,
            'rLeg' =>

                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_r_leg-' .
                    $dir->value .
                    '.png'
                ,
            'rHand' =>

                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_r_hand-' .
                    $dir->value .
                    '.png'
                ,
            'lHand' =>

                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_l_hand-' .
                    $dir->value .
                    '.png'
                ,
            'head' =>

                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_head-' .
                    $dir->value .
                    '.png'
                ,
            'chest' =>

                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_chest_f-' .
                    $dir->value .
                    '.png'

        ];
        if ($gender == 'male') {
            $sprites['chest'] =
                Path::join($this->partsDir, static::SPRITE_PREFIX) .
                '_chest_m-' .
                $dir->value .
                '.png';
        }
        return array_reverse($sprites);
    }
}
