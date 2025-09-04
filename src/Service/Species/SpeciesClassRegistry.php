<?php

namespace App\Service\Species;

use App\Attribute\SpeciesClass;
use App\Entity\Badger\Species\Species;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

class SpeciesClassRegistry
{
    public function __construct(
        private ParameterBagInterface $parameter
    ) {}

    public function getSpeciesClasses(): array
    {
        $species = [];
        $dir =
            $this->parameter->get('kernel.project_dir') .
            '/src/Entity/Badger/Species';

        $finder = new Finder();
        $finder->files()->in($dir)->name('*.php');

        foreach ($finder as $file) {
            $class =
                'App\\Entity\\Badger\\Species\\' . $file->getBasename('.php');

            if (!class_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);
            if (!$reflection->isInstantiable()) {
                continue;
            }

            $attribute =
                $reflection->getAttributes(SpeciesClass::class)[0] ?? null;
            if (!$attribute) {
                continue;
            }

            $args = $attribute->getArguments();
            if (empty($args['name'])) {
                throw new InvalidArgumentException(sprintf(
                    'The `name` argument is required for the SpeciesClass attribute on class %s.',
                    $class
                ));
            }

            $speciesName = $args['name'];
            $species[$speciesName] = $class;
        }
        return $species;
    }
}
