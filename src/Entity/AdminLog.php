<?php

namespace App\Entity;

use App\Enum\Info\AdminLogOperation;
use DateTime;
use DateTimeImmutable;

class AdminLog
{
    public function __construct(
        private int $id,
        private DateTimeImmutable $datetime,
        private ?int $round,
        private Player $admin,
        private Player $target,
        private AdminLogOperation $action,
        private string $log
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDatetime(): DateTimeImmutable
    {
        return $this->datetime;
    }

    public function getRound(): ?int
    {
        return $this->round;
    }

    public function getAdmin(): Player
    {
        return $this->admin;
    }

    public function getTarget(): Player
    {
        return $this->target;
    }

    public function getAction(): AdminLogOperation
    {
        return $this->action;
    }

    public function getLog(): string
    {
        return $this->log;
    }
}
