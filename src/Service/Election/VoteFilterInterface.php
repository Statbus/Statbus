<?php

namespace App\Service\Election;

use App\Entity\Election\Election;
use App\Entity\Player;
use App\Enum\Election\VoteType;

interface VoteFilterInterface
{
    public function getVoterType(
        string|Player $player,
        Election $election
    ): VoteType;
}
