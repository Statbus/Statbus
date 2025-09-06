<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;
use App\Service\Icons\RenderDMI;

#[SpeciesClass(name: 'Abductor')]
class Abductor extends Species
{
    public const SPRITE_PREFIX = 'abductor';

    public bool $canColor = false;

    public bool $gendered = false;

    public function __construct(
        private RenderDMI $renderDMI
    ) {
        $this->partsDir = $renderDMI->getOutputDir() . '/mob/human/bodyparts/';
    }
}
