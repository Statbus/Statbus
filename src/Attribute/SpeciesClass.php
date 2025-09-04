<?php

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SpeciesClass
{
    public string $name;
}
