<?php


namespace App\Entity;

use App\Enum\Ban\BanStatus;
use DateInterval;
use DateTimeImmutable;
use IPTools\IP;

class Ban
{

    private bool $roleBans;
    private BanStatus $status;
    private array $rules = [];

    public function __construct(
        private int $id,
        private DateTimeImmutable $bantime,
        private ?IP $ip,
        private ?int $cid,
        private int $round,
        private array|string $roles,
        private ?DateTimeImmutable $expiration,
        private ?DateTimeImmutable $unbanned,
        private string $reason,
        private ?Player $target,
        private Player $admin,
        private ?Player $unbanner,
        private ?Server $server,
        private array $banIds,
    ) {
        $this->parseRoles();
        $this->setStatus();
        $this->extractRules();
    }

    private function parseRoles(): static
    {
        $this->roles = explode(', ', $this->roles);
        return $this;
    }

    private function setStatus(): self
    {
        if ($this->unbanner) {
            $this->status = BanStatus::LIFTED;
            return $this;
        }
        if (!$this->expiration) {
            $this->status = BanStatus::PERMANENT;
            return $this;
        }
        if ($this->expiration > new DateTimeImmutable()) {
            $this->status = BanStatus::ACTIVE;
            return $this;
        } else {
            $this->status = BanStatus::EXPIRED;
            return $this;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAdmin(): Player
    {
        return $this->admin;
    }

    public function getTarget(): ?Player
    {
        return $this->target;
    }

    public function getUnbanner(): ?Player
    {
        return $this->unbanner;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getIp(): ?IP
    {
        return $this->ip;
    }

    public function getCid(): ?int
    {
        return $this->cid;
    }

    public function getBantime(): DateTimeImmutable
    {
        return $this->bantime;
    }

    public function getUnbannedTime(): ?DateTimeImmutable
    {
        return $this->unbanned;
    }

    public function getRound(): int
    {
        return $this->round;
    }

    public function getStatus(): BanStatus
    {
        return $this->status;
    }

    public function getReason(): string
    {
        return str_replace("\n", "  \n", $this->reason);
    }

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function getExpiration(): ?DateTimeImmutable
    {
        return $this->expiration;
    }

    public function getDuration(): ?DateInterval
    {
        if ($this->getExpiration()) {
            return $this->getExpiration()->diff($this->getBantime());
        }
        return null;
    }

    public function getBanIds(): array
    {
        return $this->banIds;
    }

    public function extractRules(): void
    {
        $rules = [];
        preg_match_all("/(rule|Rule|r|R) {0,1}?(\d{1,2})/", $this->getReason(), $rules);
        sort($rules[2]);
        $this->rules = array_unique($rules[2]);
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function censor(): static
    {
        $this->clearCid();
        $this->clearIp();
        return $this;
    }

    private function clearIp(): static
    {
        $this->ip = null;
        return $this;
    }

    private function clearCid(): static
    {
        $this->cid = null;
        return $this;
    }
}
