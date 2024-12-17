<?php

namespace App\Entity;

use App\Enum\Player\StandingEnum;
use App\Security\User;
use DateTimeImmutable;
use IPTools\IP;
use Symfony\Component\HttpFoundation\IpUtils;

class Player extends User
{

    private array $standing = [
        'bans' => null,
        'standing' => StandingEnum::NOT_BANNED
    ];

    private IP $ip;
    private int $cid;

    public function __construct(
        string $ckey,
        private DateTimeImmutable $firstSeen,
        private DateTimeImmutable $lastSeen,
        private ?DateTimeImmutable $accountJoinDate = null,
        ?int $flags = 0,
        ?Rank $rank = null,
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
    ) {
        $player = new self(
            ckey: $ckey,
            flags: $flags,
            rank: $rank,
            firstSeen: new DateTimeImmutable($firstSeen),
            lastSeen: new DateTimeImmutable($lastSeen),
            accountJoinDate: $accountJoinDate ? new DateTimeImmutable($accountJoinDate) : null
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
}
