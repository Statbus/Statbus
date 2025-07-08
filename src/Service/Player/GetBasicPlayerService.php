<?php

namespace App\Service\Player;

use App\Entity\Player;
use App\Service\RankService;

class GetBasicPlayerService
{
    public function __construct(
        private RankService $rankService
    ) {}

    public function playerFromCkey(string $ckey, ?string $rank): Player
    {
        return Player::newDummyPlayer(
            $ckey,
            $this->rankService->getRankByName($rank)
        );
    }
}
