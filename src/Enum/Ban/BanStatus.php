<?php

namespace App\Enum\Ban;

enum BanStatus: string
{
    case EXPIRED = 'Expired';
    case LIFTED = 'Lifted';
    case ACTIVE = 'Active';
    case PERMANENT = 'Permanent';

    public function getCssClass(): string
    {
        return match ($this) {
            BanStatus::EXPIRED => 'success',
            BanStatus::LIFTED => 'info',
            BanStatus::ACTIVE => 'danger',
            BanStatus::PERMANENT => 'perma'
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            BanStatus::EXPIRED => 'fas fa-check',
            BanStatus::LIFTED => 'fas fa-thumbs-up',
            BanStatus::ACTIVE => 'fas fa-times',
            BanStatus::PERMANENT => 'fas fa-ban'
        };
    }

    public function getArticle(): string
    {
        return match ($this) {
            BanStatus::EXPIRED => 'an',
            BanStatus::LIFTED => 'a',
            BanStatus::ACTIVE => 'an',
            BanStatus::PERMANENT => 'a'
        };
    }
}
