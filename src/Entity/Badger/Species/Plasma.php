<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;
use App\Enum\Badger\Directions;
use App\Service\Icons\RenderDMI;

#[SpeciesClass(name: 'Plasma')]
class Plasma extends Species
{
    public const SPRITE_PREFIX = 'plasmaman';

    public bool $gendered = false;

    public bool $canColor = false;

    public function __construct(
        private RenderDMI $renderDMI
    ) {
        $this->partsDir =
            $renderDMI->getOutputDir() .
            '/mob/human/species/plasmaman/bodyparts/';
    }
}
