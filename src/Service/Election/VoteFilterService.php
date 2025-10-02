<?php

namespace App\Service\Election;

use App\Entity\Election\Election;
use App\Entity\Player;
use App\Enum\Election\VoteType;
use App\Repository\PlayerRepository;
use App\Security\User;

class VoteFilterService implements VoteFilterInterface
{
    public function __construct(
        private PlayerRepository $playerRepository
    ) {}

    public function getVoterType(
        string|User $player,
        Election $election
    ): VoteType {
        if (!($player instanceof User)) {
            $player = $this->playerRepository->findByCkey($player);
        }
        if ($player->hasRole('ROLE_BAN')) {
            return VoteType::ADMIN;
        }
        if ($election->hasFilter()) {
            $class = sprintf(
                "\\App\\Service\\Election\\Filters\\Election%s",
                $election->getId()
            );
            return (new $class($this->playerRepository))->getVoterType(
                $player,
                $election
            );
        }
        return VoteType::PLAYER;
    }
}
