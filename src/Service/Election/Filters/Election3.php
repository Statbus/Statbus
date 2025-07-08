<?php

namespace App\Service\Election\Filters;

use App\Entity\Election\Election;
use App\Entity\Player;
use App\Enum\Election\VoteType;
use App\Service\Election\VoteFilterService;

class Election3 extends VoteFilterService
{
    public function getVoterType(
        string|Player $player,
        Election $election
    ): VoteType {
        return VoteType::INELIGIBLE;
    }
}
