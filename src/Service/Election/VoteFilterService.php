<?php

namespace App\Service\Election;

use App\Entity\Election\Election;
use App\Entity\Player;
use App\Enum\Election\VoteType;
use App\Repository\PlayerRepository;

class VoteFilterService implements VoteFilterInterface
{
    public function __construct(
        private PlayerRepository $playerRepository
    ) {}

    public function getVoterType(
        string|Player $player,
        Election $election
    ): VoteType {
        if (!($player instanceof Player)) {
            $player = $this->playerRepository->findByCkey($player);
        }
        if ($player->hasRole('ROLE_BAN')) {
            return VoteType::ADMIN;
        }
        return VoteType::PLAYER;
    }
}
