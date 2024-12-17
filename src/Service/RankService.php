<?php

namespace App\Service;

use App\Entity\Rank;
use Symfony\Component\Yaml\Yaml;

class RankService
{

    private array $ranks;

    public function __construct()
    {
        $this->fetchRanks();
    }

    public function getRanks(): array
    {
        if (empty($this->ranks)) {
            $this->fetchRanks();
        }
        return $this->ranks;
    }

    public function getRankByName(?string $name): Rank
    {
        if (!$name) {
            return Rank::getPlayerRank();
        }
        if (empty($this->ranks)) {
            $this->fetchRanks();
        }
        if (str_contains($name, '+')) {
            $name = explode('+', $name)[0];
        }
        if (!isset($this->ranks[$name])) {
            return Rank::getPlayerRank();
        }
        return $this->ranks[$name];
    }

    private function fetchRanks(): static
    {
        $this->ranks = Yaml::parseFile(dirname(__DIR__) . "/../assets/ranks.json");
        foreach ($this->ranks as $k => &$v) {
            $v = new Rank($k, $v['backColor'], $v['icon']);;
        }
        return $this;
    }
}
