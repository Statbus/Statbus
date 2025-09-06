<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;
use App\Enum\Badger\Directions;
use App\Service\Icons\RenderDMI;

#[SpeciesClass(name: 'Moth')]
class Moth extends Species
{
    public const SPRITE_PREFIX = 'moth';

    public bool $canColor = false;

    public function __construct(
        private RenderDMI $renderDMI
    ) {
        $this->partsDir =
            $renderDMI->getOutputDir() . '/mob/human/species/moth/bodyparts';
    }

    public function getSpriteIcons(
        string $gender = 'male',
        Directions $dir = Directions::SOUTH
    ): array {
        $sprites = parent::getSpriteIcons($gender, $dir);
        $sprites['head'] = str_replace(['_m-', '_f-'], '-', $sprites['head']);
        return $sprites;
    }
}
