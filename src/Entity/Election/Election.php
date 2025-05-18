<?php

namespace App\Entity\Election;

use App\Entity\Player;
use CondorcetPHP\Condorcet\Result;
use DateTimeImmutable;

class Election
{

    private Result $result;
    private $winner;
    private bool $filter = false;

    public function __construct(
        private int $id,
        private string $name,
        private DateTimeImmutable $start,
        private DateTimeImmutable $end,
        private Player $creator,
        private DateTimeImmutable $created,
        private ?array $candidates = null,
        private ?array $votes = null
    ) {
        $this->filter = class_exists(sprintf("\App\Service\Election\Filters\Election%s", $id));
    }

    public function started(): bool
    {
        return $this->start < new DateTimeImmutable();
    }

    public function over(): bool
    {
        return $this->end < new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    public function getCandidates(?string $by = null): ?array
    {
        if ('name' === $by) {
            $tmp = [];
            foreach ($this->candidates as $c) {
                $tmp[$c->getName()] = $c;
            }
            return $tmp;
        }
        if ('id' === $by) {
            $tmp = [];
            foreach ($this->candidates as $c) {
                $tmp[$c->getId()] = $c;
            }
            return $tmp;
        }
        return $this->candidates;
    }

    public function getVotes(): ?array
    {
        return $this->votes;
    }

    public function setResult(Result $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function getResult(): Result
    {
        return $this->result;
    }

    public function setWinner($winner): static
    {
        $this->winner = $winner;
        return $this;
    }

    public function getWinner()
    {
        return $this->winner;
    }

    public function hasFilter(): bool
    {
        return $this->filter;
    }
}
