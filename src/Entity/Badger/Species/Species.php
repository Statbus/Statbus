<?php

namespace App\Entity\Badger\Species;

use App\Attribute\SpeciesClass;
use App\Enum\Badger\Directions;
use ReflectionClass;

class Species
{
    public string $name;
    public string $path;
    public string $prefix;
    public bool $gendered = true;
    public bool $canColor = true;

    public array $extraPaths = [
        'behindFront' => [],
        'body' => []
    ];

    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        $attribute = $reflection->getAttributes(SpeciesClass::class)[0] ?? null;
        if ($attribute) {
            $this->name = $attribute->getArguments()['name'];
        }
    }

    public function getBodySprites(
        Directions $dir = Directions::SOUTH,
        ?string $gender = 'male'
    ): array {
        $sprites = [
            'rArm' => "$this->path/" . $this->prefix . "_r_arm-$dir->value.png",
            'lArm' => "$this->path/" . $this->prefix . "_l_arm-$dir->value.png",
            'lLeg' => "$this->path/" . $this->prefix . "_l_leg-$dir->value.png",
            'rLeg' => "$this->path/" . $this->prefix . "_r_leg-$dir->value.png",
            'rHand' =>
                "$this->path/" . $this->prefix . "_r_hand-$dir->value.png",
            'lHand' =>
                "$this->path/" . $this->prefix . "_l_hand-$dir->value.png",
            'head' =>
                "$this->path/" . $this->prefix . "_head_f-$dir->value.png",
            'chest' =>
                "$this->path/" . $this->prefix . "_chest_f-$dir->value.png"
        ];
        if ($gender == 'male') {
            $sprites['head'] =
                "$this->path/" . $this->prefix . "_head_m-$dir->value.png";
            $sprites['chest'] =
                "$this->path/" . $this->prefix . "_chest_m-$dir->value.png";
        }
        if (!$this->gendered) {
            $sprites['head'] = str_replace(['_m', '_f'], '', $sprites['head']);
            $sprites['chest'] = str_replace(
                ['_m', '_f'],
                '',
                $sprites['chest']
            );
        }
        return $sprites;
    }
}
