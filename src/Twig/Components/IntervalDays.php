<?php

namespace App\Twig\Components;

use DateInterval;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class IntervalDays
{
    public readonly DateInterval $interval;
}
