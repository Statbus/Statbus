<?php

namespace App\Entity\Map;

class Symbol
{
    public function __construct(
        public string $key,
        public string $turf,
        public string $area,
        public array $contents
    ) {}

    public function turfHas(string $search): bool
    {
        return str_contains($this->turf, $search);
    }

    public function areaHas(string $search): bool
    {
        return str_contains($this->area, $search);
    }

    public function contentsHas(string $search): bool
    {
        foreach ($this->contents as $c) {
            if (str_contains($c['path'], $search)) {
                return true;
            }
        }
        return false;
    }
}
