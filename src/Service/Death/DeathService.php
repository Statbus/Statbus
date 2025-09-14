<?php

namespace App\Service\Death;

use App\Entity\Round;
use App\Repository\DeathRepository;

class DeathService
{
    public function __construct(
        private DeathRepository $deathRepository
    ) {}

    public function getDeathsForRound(Round $round): array
    {
        return $this->deathRepository->fetchDeathsForRound($round);
    }
}
