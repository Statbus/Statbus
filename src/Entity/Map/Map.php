<?php

namespace App\Entity\Map;

class Map
{
    public function __construct(
        private array $symbols,
        private array $map,
        private int $zLevels = 1
    ) {}

    public function getSymbols(): array
    {
        return $this->symbols;
    }

    public function getMap(): array
    {
        return $this->map;
    }

    public function getZLevels(): int
    {
        return $this->zLevels;
    }
}
