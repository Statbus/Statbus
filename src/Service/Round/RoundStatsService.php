<?php

namespace App\Service\Round;

use App\Entity\Round;
use App\Entity\Stat;
use App\Repository\StatRepository;

class RoundStatsService
{
    public function __construct(
        private StatRepository $statRepository
    ) {}

    public function getRoundStats(Round $round, array $stats)
    {
        $results = [];
        $data = $this->statRepository->getStatsForRound($round, $stats);
        foreach ($data as $d) {
            $results[$d->getKey()] = $d;
        }
        return $results;
    }

    public function listStatsForRound(Round $round): array
    {
        return $this->statRepository->listStatsForRound($round);
    }

    public function getStatForRound(Round $round, string $stat): Stat
    {
        return $this->statRepository->fetchStatForRound($round, $stat);
    }
}
