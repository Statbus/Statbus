<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;
use App\Enum\Badger\Directions;
use App\Service\Icons\RenderDMI;

// #[SpeciesClass(name: 'Alien')]
class Alien extends Species
{
    public const SPRITE_PREFIX = 'alien';

    public bool $gendered = false;

    public function __construct(
        private RenderDMI $renderDMI
    ) {
        $this->partsDir =
            $renderDMI->getOutputDir() . '/mob/human/species/alien/bodyparts/';
    }

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
