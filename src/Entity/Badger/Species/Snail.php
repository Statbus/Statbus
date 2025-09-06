<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Service\Icons\RenderDMI;

#[SpeciesClass(name: 'Snail')]
class Snail extends Species
{
    public const SPRITE_PREFIX = 'snail';

    public bool $gendered = false;

    public function __construct(
        private RenderDMI $renderDMI
    ) {
        $this->partsDir =
            $renderDMI->getOutputDir() . '/mob/human/bodyparts_greyscale/';
    }
}
