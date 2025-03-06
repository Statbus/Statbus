<?php

namespace App\Entity;

use App\Enum\Poll\Type;
use DateTimeImmutable;
use App\Entity\Player;
use CondorcetPHP\Condorcet\Candidate;
use CondorcetPHP\Condorcet\Election;
use CondorcetPHP\Condorcet\Result;

class Poll
{

    private ?array $options;
    private ?array $votes;

    private Candidate|array $winner;
    private Result|array $result;
    private int $voteCount;

    public function __construct(
        private int $id,
        private Type $type,
        private DateTimeImmutable $created,
        private DateTimeImmutable $start,
        private DateTimeImmutable $end,
        private string $question,
        private ?string $subtitle,
        private Player $creator,
        private bool $adminonly = false,
        private bool $dontshow = false
    ) {}

    public static function new(array $data): self
    {
        return new self(
            id: $data['id'],
            type: Type::from($data['type']),
            created: new DateTimeImmutable($data['created']),
            start: new DateTimeImmutable($data['start']),
            end: new DateTimeImmutable($data['end']),
            question: $data['question'],
            subtitle: $data['subtitle'],
            creator: Player::newDummyPlayer($data['creator'], Rank::getPlayerRank())
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setVotes(array $votes): static
    {
        $this->votes = $votes;
        return $this;
    }

    public function getVotes(): array
    {
        return $this->votes;
    }

    public function setWinner(Candidate|array $winner): static
    {
        $this->winner = $winner;
        return $this;
    }

    public function setResults(Result|array $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function getWinner(): Candidate|array
    {
        return $this->winner;
    }

    public function getResult(): Result|array
    {
        return $this->result;
    }

    public function setVoteCount(int $count): static
    {
        $this->voteCount = $count;
        return $this;
    }

    public function getVoteCount(): int
    {
        return $this->voteCount;
    }

    public function getOptionsAsArray(): array
    {
        return array_combine(
            array_map(fn($o) => $o->getId(), $this->options),
            $this->options
        );
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setSubtitle(string $text): static
    {
        $this->subtitle = $text;
        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    public function isActive(): bool
    {
        return $this->end > new DateTimeImmutable();
    }
}
