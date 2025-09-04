<?php

namespace App\Form\DataTransformer;

use App\Entity\Badger\Species\Species;
use App\Factory\SpeciesFactory;
use Symfony\Component\Form\DataTransformerInterface;

class SpeciesTransformer implements DataTransformerInterface
{
    public function __construct(
        private SpeciesFactory $factory
    ) {}

    public function transform(mixed $value): mixed
    {
        // Model → Form
        return ($value instanceof Species) ? get_class($value) : '';
    }

    public function reverseTransform(mixed $value): mixed
    {
        // Form → Model
        return $value ? $this->factory->create($value) : null;
    }
}
