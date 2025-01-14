<?php

namespace App\Enum\Player;

enum StandingEnum: string
{
    case NOT_BANNED = 'No active bans';
    case ACTIVE_BANS = 'Active bans';
    case PERMABANNED = 'Permabanned';
    case NONE = 'No Standing';

    public function getCssClass(): string
    {
        return match ($this) {
            StandingEnum::NOT_BANNED => 'success text-white',
            StandingEnum::ACTIVE_BANS => 'danger',
            StandingEnum::PERMABANNED => 'perma'
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            StandingEnum::NOT_BANNED => 'circle-check',
            StandingEnum::ACTIVE_BANS => 'ban',
            StandingEnum::PERMABANNED => 'bolt-lightning'
        };
    }
}
