<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;
use App\Service\Icons\RenderDMI;

#[SpeciesClass(name: 'Ethereal')]
class Ethereal extends Species
{
    public const SPRITE_PREFIX = 'ethereal';

    public bool $gendered = false;

    public bool $canColor = false;

    public function __construct(
        private RenderDMI $renderDMI
    ) {
        $this->partsDir =
            $renderDMI->getOutputDir() .
            '/mob/human/species/ethereal/bodyparts/';
    }
}
