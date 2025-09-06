<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Enum\Badger\Directions;
use App\Service\Icons\RenderDMI;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Filesystem\Path;

class Species
{
    public const SPRITE_PREFIX = '';
    public const SKINTONES = null;

    public bool $canColor = true;
    public bool $gendered = true;

    public string $partsDir;

    public function __construct(
        private RenderDMI $renderDMI
    ) {
        $this->partsDir =
            $renderDMI->getOutputDir() . '/mob/human/bodyparts_greyscale';
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

        ];
        if ($this->gendered) {
            if ($gender == 'male') {
                $sprites['head'] =
                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_head_m-' .
                    $dir->value .
                    '.png';
                $sprites['chest'] =
                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_chest_m-' .
                    $dir->value .
                    '.png';
            } else {
                $sprites['head'] =
                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_head_f-' .
                    $dir->value .
                    '.png';
                $sprites['chest'] =
                    Path::join($this->partsDir, static::SPRITE_PREFIX) .
                    '_chest_f-' .
                    $dir->value .
                    '.png';
            }
        } else {
            $sprites['head'] =
                $this->partsDir .
                static::SPRITE_PREFIX .
                '_head-' .
                $dir->value .
                '.png';
            $sprites['chest'] =
                $this->partsDir .
                static::SPRITE_PREFIX .
                '_chest-' .
                $dir->value .
                '.png';
        }
        return array_reverse($sprites);
    }

    public static function getSpeciesName(string $class): string
    {
        $reflection = new ReflectionClass($class);
        $attributes = $reflection->getAttributes(SpeciesClass::class);

        if (count($attributes) === 0) {
            throw new RuntimeException(
                "Class $class is missing a SpeciesClass attribute"
            );
        }

        /** @var SpeciesClass $attrInstance */
        $attrInstance = $attributes[0]->newInstance();
        return $attrInstance->name;
    }
}
