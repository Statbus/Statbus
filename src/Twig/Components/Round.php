<?php

namespace App\Twig\Components;

use App\Entity\Round as RoundEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Round
{
    public Round|int $round;

    public function mount(RoundEntity|int $round): void
    {
        if ($round instanceof RoundEntity) {
            $this->round = $round->getId();
        }
    }
}
