<?php

namespace App\Entity;

use App\Entity\Player;
use DateTimeImmutable;

class Vote
{
    public function __construct(
        private int $option,
        private Player $player,
        private string $text,
        private ?DateTimeImmutable $datetime
    ) {
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getText(): string
    {
        return $this->text;
    }

    private function sanitizeName(): string
    {
        return preg_replace(
            '/<|>|\n|\t|\0|\^|\*|\$|:|;|(\|\|)|"|#/',
            '',
            strip_tags($this->text)
        );
    }

    public function getOption(): int
    {
        return $this->option;
    }

    public function getDatetime(): ?DateTimeImmutable
    {
        return $this->datetime;
    }
}
