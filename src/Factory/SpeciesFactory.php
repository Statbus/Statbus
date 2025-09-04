<?php

namespace App\Factory;

use App\Entity\Badger\Species\Species;
use App\Service\Icons\RenderDMI;

class SpeciesFactory
{
    public function __construct(
        private RenderDMI $renderDMI
    ) {}

    public function create(string $className): Species
    {
        /** @var Species $species */
        $species = new $className($this->renderDMI);
        return $species;
    }
}
