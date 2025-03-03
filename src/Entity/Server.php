<?php


namespace App\Entity;

class Server
{
    public function __construct(
        private string $name,
        private string $identifier,
        private int $port,
        private ?string $publicLogs,
        private ?string $rawLogs,
        private string $address,
        private ?int $round
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getIdentifier(): string
    {
        return strtolower($this->identifier);
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getUrl(bool $protocol = false): string
    {
        if ($protocol) {
            return "byond://" . $this->getAddress() . ":" . $this->getPort();
        }
        return $this->getAddress() . ":" . $this->getPort();
    }

    public function getPublicLogs(): ?string
    {
        return $this->publicLogs;
    }

    public function getRawLogs(): ?string
    {
        return $this->rawLogs;
    }

    public function setRound(int $round): static
    {
        $this->round = $round;
        return $this;
    }

    public function getRound(): ?int
    {
        return $this->round;
    }
}
