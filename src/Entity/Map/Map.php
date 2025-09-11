<?php

namespace App\Entity\Map;

class Map
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $dmmPath,
        public readonly string $outDir,
        public readonly array $levels = [2 => null]
    ) {}
}
