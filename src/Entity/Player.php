<?php

namespace App\Entity;

use App\Enum\Player\StandingEnum;
use App\Security\User;
use DateTimeImmutable;
use IPTools\IP;

class Player extends User
{

    private array $standing = [
        'bans' => null,
        'standing' => StandingEnum::NONE
    ];

    private ?IP $ip;
    private ?int $cid;

    public function __construct(
        string $ckey,
        private DateTimeImmutable $firstSeen,
        private DateTimeImmutable $lastSeen,
        private ?DateTimeImmutable $accountJoinDate = null,
        private ?int $flags = 0,
        private ?Rank $rank = null,
        private int $living = 0,
        private int $ghost = 0,
        private int $rounds = 0,
        private int $deaths = 0
    ) {
        parent::__construct(
            ckey: $ckey,
            flags: $flags,
            rank: $rank
        );
    }

    public static function newPlayer(
        string $ckey,
        string $firstSeen,
        string $lastSeen,
        int $ip,
        int $cid,
        ?string $accountJoinDate,
        ?int $flags = 0,
        ?Rank $rank = null,
        int $living = 0,
        int $ghost = 0,
        int $rounds = 0,
        int $deaths = 0
    ) {
        $player = new self(
            ckey: $ckey,
            flags: $flags,
            rank: $rank,
            firstSeen: new DateTimeImmutable($firstSeen),
            lastSeen: new DateTimeImmutable($lastSeen),
            accountJoinDate: $accountJoinDate ? new DateTimeImmutable($accountJoinDate) : null,
            living: $living ?? 0,
            ghost: $ghost ?? 0,
            rounds: $rounds,
            deaths: $deaths
        );
        $player->setCid($cid)->setIp($ip);
        return $player;
    }

    public static function newDummyPlayer(
        string $ckey,
        Rank $rank
    ): static {
        return new static(
            ckey: $ckey,
            flags: 0,
            rank: $rank,
            firstSeen: new DateTimeImmutable(),
            lastSeen: new DateTimeImmutable()
        );
    }

    public function getFirstSeen(): DateTimeImmutable
    {
        return $this->firstSeen;
    }

    public function getLastSeen(): DateTimeImmutable
    {
        return $this->lastSeen;
    }

    public function getAccountJoinDate(): ?DateTimeImmutable
    {
        return $this->accountJoinDate;
    }

    public function getStanding(): array
    {
        return $this->standing;
    }

    public function setStanding(array $standing): static
    {
        $this->standing = $standing;

        return $this;
    }

    public function getIp(): IP
    {
        return $this->ip;
    }

    public function getCid(): int
    {
        return $this->cid;
    }

    public function setIp(mixed $ip): self
    {
        $this->ip = IP::parse($ip);

        return $this;
    }

    public function setCid(int $cid): self
    {
        $this->cid = $cid;

        return $this;
    }

    public function censor(): static
    {
        $this->cid = null;
        $this->ip = null;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getCkey();
    }

    public function getLiving(): ?int
    {
        return $this->living;
    }

    public function getGhost(): ?int
    {
        return $this->ghost;
    }

    public function getRounds(): int
    {
        return $this->rounds;
    }

    public function getDeaths(): int
    {
        return $this->deaths + rand(1, 10);
    }
}
