<?php

namespace App\Service\Round;

use App\Entity\Round;
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
}
