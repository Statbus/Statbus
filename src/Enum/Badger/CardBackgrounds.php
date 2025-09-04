<?php

namespace App\Enum\Badger;

use App\Entity\Badger\CardTextColors;

enum CardBackgrounds: string
{
    case DEFAULT = 'default';
    case OLD = 'old';
    case LAVA = 'lava';
    case OCEAN = 'ocean';
    case ICE = 'ice';
    case HEAD_OF_STAFF = 'head';
    case CAPTAIN = 'captain';
    case CENTCOM = 'centcom';

    public function colorMap(): CardTextColors
    {
        return match ($this) {
            self::DEFAULT => new CardTextColors(
                title: '#3b3b3b',
                title_b: '#939393',
                job: '#3b3b3b',
                job_b: '#939393',
                bottom: '#939393',
                bottom_b: '#3b3b3b',
                useborder: true
            ),
            self::HEAD_OF_STAFF => new CardTextColors(
                title: '#f5ce68',
                title_b: '#939393',
                job: '#f5ce68',
                job_b: '#939393',
                bottom: '#939393',
                bottom_b: '#3b3b3b',
                useborder: true
            ),
            self::CENTCOM => new CardTextColors(
                title: '#5d0000',
                title_b: '#939393',
                job: '#5d0000',
                job_b: '#939393',
                bottom: '#939393',
                bottom_b: '#3b3b3b',
                useborder: true
            ),
            self::OCEAN => new CardTextColors(
                title: '#b7bace',
                job: '#c8cad9',
                bottom: '#b7bace'
            ),
            self::LAVA, self::OLD => new CardTextColors(
                title: '#c4dfe1',
                job: '#ffffff',
                bottom: '#c4dfe1'
            ),
            self::ICE => new CardTextColors(
                title: '#3e467a',
                job: '#5964ab',
                bottom: '#3e467a'
            ),
            self::CAPTAIN => new CardTextColors(
                title: '#4a3800',
                job: '#4a3800',
                bottom: '#6a5500',
                transform: fn($d) => $d->job = strtoupper($d->job)
            )
        };
    }
}
