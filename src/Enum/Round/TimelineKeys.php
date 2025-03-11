<?php


namespace App\Enum\Round;

enum TimelineKeys: string
{
    case ROUND_START = 'round_start';
    case ROUND_END = 'round_end';
    case EXPLOSION = 'explosion';
    case DEATH = 'death';
    case TCOMMS = 'tcomms';
    case MANIFEST = 'manifest';
    case DYNAMIC = 'dynamic';
    case SHUTTLE = 'shu';

    public function getIcon(): string
    {
        return match ($this) {
            TimelineKeys::ROUND_START => 'fa-solid fa-play',
            TimelineKeys::ROUND_END => 'fa-solid fa-rocket',
            TimelineKeys::EXPLOSION => 'fa-solid fa-explosion text-danger',
            TimelineKeys::DEATH => 'fa-solid fa-skull',
            TimelineKeys::TCOMMS => 'fa-solid fa-walkie-talkie',
            TimelineKeys::MANIFEST => 'fa-solid fa-briefcase',
            TimelineKeys::DYNAMIC => 'fa-solid fa-dice',
            TimelineKeys::SHUTTLE => 'fa-solid fa-shuttle-space'
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            TimelineKeys::ROUND_START => 'text-bg-info',
            TimelineKeys::ROUND_END => 'text-bg-info',
            TimelineKeys::EXPLOSION => 'text-bg-danger',
            TimelineKeys::DEATH => 'text-bg-dark',
            TimelineKeys::TCOMMS => 'text-bg-primary',
            TimelineKeys::MANIFEST => 'text-bg-primary',
            TimelineKeys::DYNAMIC => 'text-bg-primary',
            TimelineKeys::SHUTTLE => 'text-bg-primary',
        };
    }

    public function getName(): string
    {
        return match ($this) {
            TimelineKeys::ROUND_START => 'Round Start',
            TimelineKeys::ROUND_END => 'Round End',
            TimelineKeys::EXPLOSION => 'Explosion',
            TimelineKeys::DEATH => 'Death',
            TimelineKeys::TCOMMS => 'Telecomms',
            TimelineKeys::MANIFEST => 'Manifest',
            TimelineKeys::DYNAMIC => 'Dynamic',
            TimelineKeys::SHUTTLE => 'Shuttle'
        };
    }
}
