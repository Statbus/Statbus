<?php

namespace App\Entity\Election;

use App\Entity\Player;
use App\Enum\Election\AnonymityType;
use App\Enum\Election\VoteType;
use App\Security\User;
use App\Service\Election\VoteFilterService;
use CondorcetPHP\Condorcet\Result;
use DateInterval;
use DateTimeImmutable;
use ReflectionClass;

class Election
{
    private Result $result;
    private $winner;
    private bool $filter = false;
    private readonly string $filterHash;

    public function __construct(
        private int $id,
        private string $name,
        private DateTimeImmutable $start,
        private DateTimeImmutable $end,
        private Player $creator,
        private DateTimeImmutable $created,
        private AnonymityType $anonymity,
        private ?array $candidates = null,
        private ?array $votes = null
    ) {
        $file = (new ReflectionClass(VoteFilterService::class))->getFileName();

        $this->filter = class_exists(sprintf(
            "\App\Service\Election\Filters\Election%s",
            $id
        ));
        if ($this->filter) {
            $file = (new ReflectionClass(sprintf(
                "\App\Service\Election\Filters\Election%s",
                $id
            )))->getFileName();
        }
        $this->filterHash = hash('sha512', file_get_contents($file));
    }

    public function started(): bool
    {
        return $this->start < new DateTimeImmutable();
    }

    public function over(): bool
    {
        return $this->end < new DateTimeImmutable();
    }

    public function isUnderway(): bool
    {
        return $this->started() && !$this->over();
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

    public function getDuration(): DateInterval
    {
        return $this->start->diff($this->end);
    }

    public function getRemainder(): DateInterval
    {
        if ($this->started()) {
            return (new DateTimeImmutable())->diff($this->end);
        }
        return $this->getDuration();
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

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    public function getFilterHash(): string
    {
        return $this->filterHash;
    }

    public function getAnonymity(): AnonymityType
    {
        return $this->anonymity;
    }
}
