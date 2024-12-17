<?php

namespace App\Service\Player;

use App\Enum\Player\StandingEnum;
use App\Entity\Player;
use App\Repository\BanRepository;

class IsBannedService
{

    public function __construct(
        private BanRepository $banRepository
    ) {
    }

    public function isPlayerBanned(Player $player): array
    {
        $standing = [];
        $standing['bans'] = (array) $this->banRepository->getPlayerStanding($player);
        if (!$standing['bans']) {
            $standing['status'] = StandingEnum::NOT_BANNED;
            return $standing;
        }

        foreach ($standing['bans'] as $b) {
            $b = (array) $b;
            $b['perm'] = (isset($b['expiration_time'])) ? false : true;
        }
        if ($b['perm'] && 'Server' === $b['role']) {
            $standing['status'] = StandingEnum::PERMABANNED;
            return $standing;
        } else {
            $standing['status'] = StandingEnum::ACTIVE_BANS;
        }
        return $standing;
    }
}
